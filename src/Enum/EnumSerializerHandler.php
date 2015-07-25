<?php

namespace Consistence\JmsSerializer\Enum;

use Consistence\Enum\Enum;
use Consistence\Enum\MultiEnum;
use Consistence\Type\ArrayType\ArrayType;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\VisitorInterface;

use Traversable;

class EnumSerializerHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{

	const PARAM_MULTI_AS_SINGLE = 'as_single';

	const PATH_PROPERTY_SEPARATOR = '::';
	const PATH_FIELD_SEPARATOR = '.';

	const TYPE_ENUM = 'enum';

	/**
	 * @return string[][]
	 */
	public static function getSubscribingMethods()
	{
		$formats = ['json', 'xml', 'yml'];
		$methods = [];
		foreach ($formats as $format) {
			$methods[] = [
				'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
				'type' => self::TYPE_ENUM,
				'format' => $format,
				'method' => 'serializeEnum',
			];
			$methods[] = [
				'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
				'type' => self::TYPE_ENUM,
				'format' => $format,
				'method' => 'deserializeEnum',
			];
		}

		return $methods;
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param \Consistence\Enum\Enum $enum
	 * @param mixed[] $type
	 * @param \JMS\Serializer\Context $context
	 * @return mixed
	 */
	public function serializeEnum(VisitorInterface $visitor, Enum $enum, array $type, Context $context)
	{
		try {
			return $this->serializeEnumValue($enum, $type);
		} catch (\Consistence\JmsSerializer\Enum\MappedClassMismatchException $e) {
			throw new \Consistence\JmsSerializer\Enum\SerializationInvalidValueException($this->getPropertyPath($context), $e);
		}
	}

	/**
	 * @param \Consistence\Enum\Enum $enum
	 * @param mixed[] $type
	 * @return mixed
	 */
	private function serializeEnumValue(Enum $enum, array $type)
	{
		if ($this->hasEnumClassParameter($type)) {
			$mappedEnumClass = $this->getEnumClass($type);
			$actualEnumClass = get_class($enum);
			if ($mappedEnumClass !== $actualEnumClass) {
				throw new \Consistence\JmsSerializer\Enum\MappedClassMismatchException($mappedEnumClass, $actualEnumClass);
			}
			if ($this->hasAsSingleParameter($type)) {
				$this->checkMultiEnum($actualEnumClass);
				return array_values(ArrayType::mapValuesByCallback($enum->getEnums(), function (Enum $singleEnum) {
					return $singleEnum->getValue();
				}));
			}
		}

		return $enum->getValue();
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param mixed $data
	 * @param mixed[] $type
	 * @param \JMS\Serializer\Context $context
	 * @return \Consistence\Enum\Enum
	 */
	public function deserializeEnum(VisitorInterface $visitor, $data, array $type, Context $context)
	{
		try {
			return $this->deserializeEnumValue($data, $type);
		} catch (\Consistence\Enum\InvalidEnumValueException $e) {
			throw new \Consistence\JmsSerializer\Enum\DeserializationInvalidValueException($this->getFieldPath($visitor, $context), $e);
		} catch (\Consistence\JmsSerializer\Enum\NotIterableValueException $e) {
			throw new \Consistence\JmsSerializer\Enum\DeserializationInvalidValueException($this->getFieldPath($visitor, $context), $e);
		}
	}

	/**
	 * @param mixed $data
	 * @param mixed[] $type
	 * @return \Consistence\Enum\Enum
	 */
	private function deserializeEnumValue($data, array $type)
	{
		$enumClass = $this->getEnumClass($type);
		if ($this->hasAsSingleParameter($type)) {
			$this->checkMultiEnum($enumClass);
			$singleEnumClass = $enumClass::getSingleEnumClass();
			if ($singleEnumClass === null) {
				throw new \Consistence\Enum\NoSingleEnumSpecifiedException($enumClass);
			}
			$singleEnums = [];
			if (!is_array($data) && !($data instanceof Traversable)) {
				throw new \Consistence\JmsSerializer\Enum\NotIterableValueException($data);
			}
			foreach ($data as $item) {
				$singleEnums[] = $singleEnumClass::get($item);
			}

			return $enumClass::getMultiByEnums($singleEnums);
		}

		return $enumClass::get($data);
	}

	/**
	 * @param mixed[] $type
	 * @return string
	 */
	private function getEnumClass(array $type)
	{
		if (!$this->hasEnumClassParameter($type)) {
			throw new \Consistence\JmsSerializer\Enum\MissingEnumNameException();
		}
		$enumClass = $type['params'][0]['name'];
		if (!is_a($enumClass, Enum::class, true)) {
			throw new \Consistence\JmsSerializer\Enum\NotEnumException($enumClass);
		}

		return $enumClass;
	}

	/**
	 * @param mixed[] $type
	 * @return boolean
	 */
	private function hasEnumClassParameter(array $type)
	{
		return isset($type['params'][0])
			&& isset($type['params'][0]['name']);
	}

	/**
	 * @param mixed[] $type
	 * @return boolean
	 */
	private function hasAsSingleParameter(array $type)
	{
		return isset($type['params'][1])
			&& isset($type['params'][1]['name'])
			&& $type['params'][1]['name'] === self::PARAM_MULTI_AS_SINGLE;
	}

	/**
	 * @param string $enumClass
	 */
	private function checkMultiEnum($enumClass)
	{
		if (!is_a($enumClass, MultiEnum::class, true)) {
			throw new \Consistence\JmsSerializer\Enum\NotMultiEnumException($enumClass);
		}
	}

	/**
	 * @param \JMS\Serializer\Context $context
	 * @return string
	 */
	private function getPropertyPath(Context $context)
	{
		$path = '';
		$lastPropertyMetadata = null;
		foreach ($context->getMetadataStack() as $element) {
			if ($element instanceof PropertyMetadata) {
				$name = $element->name;
				$path = '$' . $name . self::PATH_PROPERTY_SEPARATOR . $path;
				$lastPropertyMetadata = $element;
			}
		}
		if ($lastPropertyMetadata !== null) {
			$path = $lastPropertyMetadata->class . self::PATH_PROPERTY_SEPARATOR . $path;
		}
		$path = rtrim($path, self::PATH_PROPERTY_SEPARATOR);

		return $path;
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param \JMS\Serializer\Context $context
	 * @return string
	 */
	private function getFieldPath(VisitorInterface $visitor, Context $context)
	{
		$path = '';
		foreach ($context->getMetadataStack() as $element) {
			if ($element instanceof PropertyMetadata) {
				$name = ($element->serializedName !== null) ? $element->serializedName : $element->name;
				if ($visitor instanceof AbstractVisitor) {
					$name = $visitor->getNamingStrategy()->translateName($element);
				}

				$path = $name . self::PATH_FIELD_SEPARATOR . $path;
			}
		}
		$path = rtrim($path, self::PATH_FIELD_SEPARATOR);

		return $path;
	}

}
