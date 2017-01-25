<?php

namespace Consistence\JmsSerializer\Enum;

use Closure;

use Consistence\Enum\Enum;
use Consistence\Enum\MultiEnum;
use Consistence\Type\ArrayType\ArrayType;
use Consistence\Type\Type;

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
			return $this->serializeEnumValue($visitor, $enum, $type, $context);
		} catch (\Consistence\JmsSerializer\Enum\MappedClassMismatchException $e) {
			throw new \Consistence\JmsSerializer\Enum\SerializationInvalidValueException($this->getPropertyPath($context), $e);
		}
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param \Consistence\Enum\Enum $enum
	 * @param mixed[] $type
	 * @param \JMS\Serializer\Context $context
	 * @return mixed
	 */
	private function serializeEnumValue(VisitorInterface $visitor, Enum $enum, array $type, Context $context)
	{
		if ($this->hasEnumClassParameter($type)) {
			$mappedEnumClass = $this->getEnumClass($type);
			$actualEnumClass = get_class($enum);
			if ($mappedEnumClass !== $actualEnumClass) {
				throw new \Consistence\JmsSerializer\Enum\MappedClassMismatchException($mappedEnumClass, $actualEnumClass);
			}
			if ($this->hasAsSingleParameter($type)) {
				$this->checkMultiEnum($actualEnumClass);
				$arrayValueType = [
					'name' => 'enum',
					'params' => [
						[
							'name' => 'enum',
							'params' => [
								[
									'name' => $mappedEnumClass::getSingleEnumClass(),
									'params' => [],
								],
							],
						],
					],
				];
				return $visitor->visitArray(array_values($enum->getEnums()), $arrayValueType, $context);
			}
		}

		return $this->serializationVisitType($visitor, $enum, $type, $context);
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param \Consistence\Enum\Enum $enum
	 * @param mixed[] $typeMetadata
	 * @param \JMS\Serializer\Context $context
	 * @return mixed
	 */
	private function serializationVisitType(VisitorInterface $visitor, Enum $enum, array $typeMetadata, Context $context)
	{
		$value = $enum->getValue();
		$valueType = EnumValueType::get(Type::getType($value));

		return $this->visitType($visitor, $value, $valueType, $typeMetadata, $context);
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param mixed $data
	 * @param \Consistence\JmsSerializer\Enum\EnumValueType $dataType
	 * @param mixed[] $typeMetadata
	 * @param \JMS\Serializer\Context $context
	 * @return mixed
	 */
	private function visitType(VisitorInterface $visitor, $data, EnumValueType $dataType, array $typeMetadata, Context $context)
	{
		switch (true) {
			case $dataType->equalsValue(EnumValueType::INTEGER):
				return $visitor->visitInteger($data, $typeMetadata, $context);
			case $dataType->equalsValue(EnumValueType::STRING):
				return $visitor->visitString($data, $typeMetadata, $context);
			case $dataType->equalsValue(EnumValueType::FLOAT):
				return $visitor->visitDouble($data, $typeMetadata, $context);
			case $dataType->equalsValue(EnumValueType::BOOLEAN):
				return $visitor->visitBoolean($data, $typeMetadata, $context);
			// @codeCoverageIgnoreStart
			// should never happen, other types are not allowed in Enums
			default:
				throw new \Exception('Unexpected type');
		}
		// @codeCoverageIgnoreEnd
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
			return $this->deserializeEnumValue($visitor, $data, $type, $context);
		} catch (\Consistence\Enum\InvalidEnumValueException $e) {
			throw new \Consistence\JmsSerializer\Enum\DeserializationInvalidValueException($this->getFieldPath($visitor, $context), $e);
		} catch (\Consistence\JmsSerializer\Enum\NotIterableValueException $e) {
			throw new \Consistence\JmsSerializer\Enum\DeserializationInvalidValueException($this->getFieldPath($visitor, $context), $e);
		}
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param mixed $data
	 * @param mixed[] $type
	 * @param \JMS\Serializer\Context $context
	 * @return \Consistence\Enum\Enum
	 */
	private function deserializeEnumValue(VisitorInterface $visitor, $data, array $type, Context $context)
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
				$singleEnums[] = $singleEnumClass::get($this->deserializationVisitType($visitor, $item, $type, $context));
			}

			return $enumClass::getMultiByEnums($singleEnums);
		}

		return $enumClass::get($this->deserializationVisitType($visitor, $data, $type, $context));
	}

	/**
	 * @param \JMS\Serializer\VisitorInterface $visitor
	 * @param mixed $data
	 * @param mixed[] $typeMetadata
	 * @param \JMS\Serializer\Context $context
	 * @return mixed
	 */
	private function deserializationVisitType(VisitorInterface $visitor, $data, array $typeMetadata, Context $context)
	{
		$deserializationType = $this->findDeserializationType($typeMetadata);
		if ($deserializationType === null) {
			return $data;
		}

		return $this->visitType($visitor, $data, $deserializationType, $typeMetadata, $context);
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
		return $this->findParameter($type, function (array $parameter) {
			return $parameter['name'] === self::PARAM_MULTI_AS_SINGLE;
		}) !== null;
	}

	/**
	 * @param mixed[] $type
	 * @return \Consistence\JmsSerializer\Enum\EnumValueType|null
	 */
	private function findDeserializationType(array $type)
	{
		$parameter = $this->findParameter($type, function (array $parameter) {
			return EnumValueType::isValidValue($parameter['name']);
		});

		if ($parameter === null) {
			return null;
		}

		return EnumValueType::get($parameter['name']);
	}

	/**
	 * @param mixed[] $type
	 * @param \Closure $callback
	 * @return mixed[]|null
	 */
	private function findParameter(array $type, Closure $callback)
	{
		return ArrayType::findValueByCallback($type['params'], $callback);
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
