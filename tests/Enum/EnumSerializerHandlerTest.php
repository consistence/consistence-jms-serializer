<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\Type;
use Generator;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\Assert;
use SimpleXMLElement;
use stdClass;

class EnumSerializerHandlerTest extends \PHPUnit\Framework\TestCase
{

	public function testSerializeEnumToJson(): void
	{
		$user = new User();
		$user->singleEnum = RoleEnum::get(RoleEnum::ADMIN);

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString('"single_enum":"admin"', $json);
	}

	public function testSerializeEnumToXml(): void
	{
		$user = new User();
		$user->singleEnum = RoleEnum::get(RoleEnum::ADMIN);

		$serializer = $this->getSerializer();
		$xml = $serializer->serialize($user, 'xml');
		Assert::assertStringContainsString('<single_enum><![CDATA[admin]]></single_enum>', $xml);
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function jsonTypeDataProvider(): Generator
	{
		yield 'integer' => [
			'value' => TypeEnum::INTEGER,
			'serializedValue' => '1',
		];
		yield 'string' => [
			'value' => TypeEnum::STRING,
			'serializedValue' => '"foo"',
		];
		yield 'float' => [
			'value' => TypeEnum::FLOAT,
			'serializedValue' => '2.5',
		];
		yield 'boolean' => [
			'value' => TypeEnum::BOOLEAN,
			'serializedValue' => 'true',
		];
	}

	/**
	 * @dataProvider jsonTypeDataProvider
	 *
	 * @param mixed $value
	 * @param string $serializedValue
	 */
	public function testSerializeJsonTypes($value, string $serializedValue): void
	{
		$user = new User();
		$user->typeEnum = TypeEnum::get($value);

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString(sprintf('"type_enum":%s', $serializedValue), $json);
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function xmlTypeDataProvider(): Generator
	{
		yield 'integer' => [
			'value' => TypeEnum::INTEGER,
			'serializedValue' => '1',
		];
		yield 'string' => [
			'value' => TypeEnum::STRING,
			'serializedValue' => '<![CDATA[foo]]>',
		];
		yield 'float' => [
			'value' => TypeEnum::FLOAT,
			'serializedValue' => '2.5',
		];
		yield 'boolean' => [
			'value' => TypeEnum::BOOLEAN,
			'serializedValue' => 'true',
		];
	}

	/**
	 * @dataProvider xmlTypeDataProvider
	 *
	 * @param mixed $value
	 * @param string $serializedValue
	 */
	public function testSerializeXmlTypes($value, string $serializedValue): void
	{
		$user = new User();
		$user->typeEnum = TypeEnum::get($value);

		$serializer = $this->getSerializer();
		$xml = $serializer->serialize($user, 'xml');
		Assert::assertStringContainsString(sprintf('<type_enum>%s</type_enum>', $serializedValue), $xml);
	}

	public function testDeserializeEnumFromJson(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize('{
			"single_enum": "admin"
		}', User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(RoleEnum::get(RoleEnum::ADMIN), $user->singleEnum);
	}

	public function testDeserializeEnumFromXml(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(
			'<?xml version="1.0" encoding="UTF-8"?>'
			. '<result>'
			. '<single_enum_with_type><![CDATA[admin]]></single_enum_with_type>'
			. '</result>',
			User::class,
			'xml'
		);
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(RoleEnum::get(RoleEnum::ADMIN), $user->singleEnumWithType);
	}

	/**
	 * @dataProvider jsonTypeDataProvider
	 *
	 * @param mixed $value
	 * @param string $serializedValue
	 */
	public function testDeserializeJsonTypes($value, string $serializedValue): void
	{
		$serializer = $this->getSerializer();
		$type = Type::getType($value);
		$user = $serializer->deserialize(sprintf('{
			"%s": %s
		}', $type, $serializedValue), User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(TypeEnum::get($value), $user->$type);
	}

	public function testSerializeMultiEnum(): void
	{
		$user = new User();
		$user->multiEnum = RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]);

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString('"multi_enum":3', $json);
	}

	public function testDeserializeMultiEnum(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize('{
			"multi_enum": 3
		}', User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]), $user->multiEnum);
	}

	public function testSerializeArrayOfEnums(): void
	{
		$user = new User();
		$user->arrayOfSingleEnums = [
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		];

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString('"array_of_single_enums":["admin","employee"]', $json);
	}

	public function testDeserializeArrayOfEnums(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize('{
			"array_of_single_enums": [
				"admin",
				"employee"
			]
		}', User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		foreach ([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		] as $expectedRole) {
			Assert::assertContains($expectedRole, $user->arrayOfSingleEnums);
		}
		Assert::assertCount(2, $user->arrayOfSingleEnums);
	}

	public function testSerializeArrayOfEnumsAsSingleEnumsArrayToJson(): void
	{
		$user = new User();
		$user->multiEnumAsSingleEnumsArray = RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]);

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString('"multi_enum_as_single_enums_array":["employee","admin"]', $json);
	}

	public function testSerializeArrayOfEnumsAsSingleEnumsArrayToXml(): void
	{
		$user = new User();
		$user->multiEnumAsSingleEnumsArrayWithType = RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]);

		$serializer = $this->getSerializer();
		$xml = $serializer->serialize($user, 'xml');
		Assert::assertStringContainsString('<entry><![CDATA[admin]]></entry>', $xml);
		Assert::assertStringContainsString('<entry><![CDATA[employee]]></entry>', $xml);
	}

	public function testDeserializeMultiEnumAsSingleEnumsArrayFromJson(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize('{
			"multi_enum_as_single_enums_array": [
				"admin",
				"employee"
			]
		}', User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]), $user->multiEnumAsSingleEnumsArray);
	}

	public function testDeserializeMultiEnumAsSingleEnumsArrayFromXml(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(
			'<?xml version="1.0" encoding="UTF-8"?>'
			. '<result>'
			. '<multi_enum_as_single_enums_array_with_type>'
			. '<entry><![CDATA[employee]]></entry>'
			. '<entry><![CDATA[admin]]></entry>'
			. '</multi_enum_as_single_enums_array_with_type>'
			. '</result>',
			User::class,
			'xml'
		);
		Assert::assertInstanceOf(User::class, $user);
		Assert::assertSame(RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]), $user->multiEnumAsSingleEnumsArrayWithType);
	}

	public function testSerializeEnumWithoutName(): void
	{
		$user = new User();
		$user->missingEnumName = RoleEnum::get(RoleEnum::ADMIN);

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		Assert::assertStringContainsString('"missing_enum_name":"admin"', $json);
	}

	public function testDeserializeEnumWithoutName(): void
	{
		$serializer = $this->getSerializer();

		$this->expectException(\Consistence\JmsSerializer\Enum\MissingEnumNameException::class);

		$serializer->deserialize('{
			"missing_enum_name": "admin"
		}', User::class, 'json');
	}

	public function testDeserializeEnumInvalidClass(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"invalid_enum_class": "admin"
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\NotEnumException $e) {
			Assert::assertSame(stdClass::class, $e->getClassName());
		}
	}

	public function testSerializeEnumInvalidValue(): void
	{
		$user = new User();
		$user->multiEnum = RoleEnum::get(RoleEnum::ADMIN);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\SerializationInvalidValueException $e) {
			Assert::assertSame(sprintf('%s::$multiEnum', User::class), $e->getPropertyPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\JmsSerializer\Enum\MappedClassMismatchException::class, $previous);
			Assert::assertSame(RolesEnum::class, $previous->getMappedClassName());
			Assert::assertSame(RoleEnum::class, $previous->getValueClassName());
		}
	}

	public function testSerializeEnumInvalidValueEmbeddedObject(): void
	{
		$embeddedUser = new User();
		$embeddedUser->multiEnum = RoleEnum::get(RoleEnum::ADMIN);

		$user = new User();
		$user->embeddedObject = $embeddedUser;
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\SerializationInvalidValueException $e) {
			Assert::assertSame(sprintf('%s::$embeddedObject::$multiEnum', User::class), $e->getPropertyPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\JmsSerializer\Enum\MappedClassMismatchException::class, $previous);
			Assert::assertSame(RolesEnum::class, $previous->getMappedClassName());
			Assert::assertSame(RoleEnum::class, $previous->getValueClassName());
		}
	}

	public function testDeserializeEnumInvalidValue(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_enum": "foo"
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumInvalidValueEmbeddedObject(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{"embedded_object": {
				"single_enum": "foo"
			}}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('embedded_object.single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumWhenValueIsArray(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_enum": [1, 2, 3]
			}', User::class, 'json');

			Assert::fail('Exception expected');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame([1, 2, 3], $previous->getValue());
		}
	}

	public function testDeserializeEnumWhenValueIsObject(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_enum": {"foo": "bar"}
			}', User::class, 'json');

			Assert::fail('Exception expected');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame(['foo' => 'bar'], $previous->getValue());
		}
	}

	public function testDeserializeMultiEnumWithInvalidValueType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"multi_enum": "foo"
			}', User::class, 'json');

			Assert::fail('Exception expected');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('multi_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame('foo', $previous->getValue());
		}
	}

	public function testSerializeEnumAsSingleEnumsArrayNotMappedSingleEnum(): void
	{
		$user = new User();
		$user->multiNoSingleEnumMapped = FooEnum::getMulti(FooEnum::FOO);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\Enum\NoSingleEnumSpecifiedException $e) {
			Assert::assertSame(FooEnum::class, $e->getClass());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNotMappedSingleEnum(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"multi_no_single_enum_mapped": 1
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\Enum\NoSingleEnumSpecifiedException $e) {
			Assert::assertSame(FooEnum::class, $e->getClass());
		}
	}

	public function testSerializeEnumAsSingleEnumsArrayNotMultiEnum(): void
	{
		$user = new User();
		$user->singleMappedAsMulti = RoleEnum::get(RoleEnum::ADMIN);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\NotMultiEnumException $e) {
			Assert::assertSame(RoleEnum::class, $e->getClassName());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNotMultiEnum(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_mapped_as_multi": [
					"admin",
					"employee"
				]
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\NotMultiEnumException $e) {
			Assert::assertSame(RoleEnum::class, $e->getClassName());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNoArrayGiven(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"multi_enum_as_single_enums_array": "foo"
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('multi_enum_as_single_enums_array', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\JmsSerializer\Enum\NotIterableValueException::class, $previous);
			Assert::assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumFromXmlWithoutDeserializationType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(
				'<?xml version="1.0" encoding="UTF-8"?>'
				. '<result>'
				. '<single_enum><![CDATA[admin]]></single_enum>'
				. '</result>',
				User::class,
				'xml'
			);
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertInstanceOf(SimpleXMLElement::class, $previous->getValue());
		}
	}

	public function testDeserializeEnumWithWrongDeserializationType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"type_enum_with_type": 1
			}', User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame('type_enum_with_type', $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame('1', $previous->getValue());
		}
	}

	private function getSerializer(): SerializerInterface
	{
		return SerializerBuilder::create()
			->configureHandlers(function (HandlerRegistry $registry): void {
				$registry->registerSubscribingHandler(new EnumSerializerHandler());
			})
			->build();
	}

}
