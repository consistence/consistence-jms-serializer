<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\Type;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
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
		$this->assertStringContainsString(sprintf('"single_enum":"%s"', RoleEnum::ADMIN), $json);
	}

	public function testSerializeEnumToXml(): void
	{
		$user = new User();
		$user->singleEnum = RoleEnum::get(RoleEnum::ADMIN);

		$serializer = $this->getSerializer();
		$xml = $serializer->serialize($user, 'xml');
		$this->assertStringContainsString(sprintf('<single_enum><![CDATA[%s]]></single_enum>', RoleEnum::ADMIN), $xml);
	}

	/**
	 * @return mixed[][]
	 */
	public function jsonTypesProvider(): array
	{
		return [
			[TypeEnum::INTEGER, '1'],
			[TypeEnum::STRING, '"foo"'],
			[TypeEnum::FLOAT, '2.5'],
			[TypeEnum::BOOLEAN, 'true'],
		];
	}

	/**
	 * @dataProvider jsonTypesProvider
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
		$this->assertStringContainsString(sprintf('"type_enum":%s', $serializedValue), $json);
	}

	/**
	 * @return mixed[][]
	 */
	public function xmlTypesProvider(): array
	{
		return [
			[TypeEnum::INTEGER, '1'],
			[TypeEnum::STRING, '<![CDATA[foo]]>'],
			[TypeEnum::FLOAT, '2.5'],
			[TypeEnum::BOOLEAN, 'true'],
		];
	}

	/**
	 * @dataProvider xmlTypesProvider
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
		$this->assertStringContainsString(sprintf('<type_enum>%s</type_enum>', $serializedValue), $xml);
	}

	public function testDeserializeEnumFromJson(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf('{
			"single_enum": "%s"
		}', RoleEnum::ADMIN), User::class, 'json');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame(RoleEnum::get(RoleEnum::ADMIN), $user->singleEnum);
	}

	public function testDeserializeEnumFromXml(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>'
			. '<result>'
			. '<single_enum_with_type><![CDATA[admin]]></single_enum_with_type>'
			. '</result>',
			RoleEnum::ADMIN
		), User::class, 'xml');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame(RoleEnum::get(RoleEnum::ADMIN), $user->singleEnumWithType);
	}

	/**
	 * @dataProvider jsonTypesProvider
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
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame(TypeEnum::get($value), $user->$type);
	}

	public function testSerializeMultiEnum(): void
	{
		$user = new User();
		$roles = RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]);
		$user->multiEnum = $roles;

		$serializer = $this->getSerializer();
		$json = $serializer->serialize($user, 'json');
		$this->assertStringContainsString(sprintf('"multi_enum":%d', $roles->getValue()), $json);
	}

	public function testDeserializeMultiEnum(): void
	{
		$roles = RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]);
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf('{
			"multi_enum": %d
		}', $roles->getValue()), User::class, 'json');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame($roles, $user->multiEnum);
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
		$this->assertStringContainsString(sprintf('"array_of_single_enums":["%s","%s"]', RoleEnum::ADMIN, RoleEnum::EMPLOYEE), $json);
	}

	public function testDeserializeArrayOfEnums(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf('{
			"array_of_single_enums": [
				"%s",
				"%s"
			]
		}', RoleEnum::ADMIN, RoleEnum::EMPLOYEE), User::class, 'json');
		$this->assertInstanceOf(User::class, $user);
		$this->assertCount(2, $user->arrayOfSingleEnums);
		foreach ([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		] as $expectedRole) {
			$this->assertContains($expectedRole, $user->arrayOfSingleEnums);
		}
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
		$this->assertStringContainsString(sprintf('"multi_enum_as_single_enums_array":["%s","%s"]', RoleEnum::EMPLOYEE, RoleEnum::ADMIN), $json);
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
		$this->assertStringContainsString(sprintf('<entry><![CDATA[%s]]></entry>', RoleEnum::ADMIN), $xml);
		$this->assertStringContainsString(sprintf('<entry><![CDATA[%s]]></entry>', RoleEnum::EMPLOYEE), $xml);
	}

	public function testDeserializeMultiEnumAsSingleEnumsArrayFromJson(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf('{
			"multi_enum_as_single_enums_array": [
				"%s",
				"%s"
			]
		}', RoleEnum::ADMIN, RoleEnum::EMPLOYEE), User::class, 'json');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame(RolesEnum::getMultiByEnums([
			RoleEnum::get(RoleEnum::ADMIN),
			RoleEnum::get(RoleEnum::EMPLOYEE),
		]), $user->multiEnumAsSingleEnumsArray);
	}

	public function testDeserializeMultiEnumAsSingleEnumsArrayFromXml(): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>'
			. '<result>'
			. '<multi_enum_as_single_enums_array_with_type>'
			. '<entry><![CDATA[employee]]></entry>'
			. '<entry><![CDATA[admin]]></entry>'
			. '</multi_enum_as_single_enums_array_with_type>'
			. '</result>',
			RoleEnum::ADMIN
		), User::class, 'xml');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame(RolesEnum::getMultiByEnums([
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
		$this->assertStringContainsString(sprintf('"missing_enum_name":"%s"', RoleEnum::ADMIN), $json);
	}

	public function testDeserializeEnumWithoutName(): void
	{
		$serializer = $this->getSerializer();

		$this->expectException(\Consistence\JmsSerializer\Enum\MissingEnumNameException::class);

		$serializer->deserialize(sprintf('{
			"missing_enum_name": "%s"
		}', RoleEnum::ADMIN), User::class, 'json');
	}

	public function testDeserializeEnumInvalidClass(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{
				"invalid_enum_class": "%s"
			}', RoleEnum::ADMIN), User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\NotEnumException $e) {
			$this->assertSame(stdClass::class, $e->getClassName());
		}
	}

	public function testSerializeEnumInvalidValue(): void
	{
		$user = new User();
		$user->multiEnum = RoleEnum::get(RoleEnum::ADMIN);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\SerializationInvalidValueException $e) {
			$this->assertEquals(sprintf('%s::$multiEnum', User::class), $e->getPropertyPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\JmsSerializer\Enum\MappedClassMismatchException::class, $previous);
			$this->assertSame(RolesEnum::class, $previous->getMappedClassName());
			$this->assertSame(RoleEnum::class, $previous->getValueClassName());
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
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\SerializationInvalidValueException $e) {
			$this->assertEquals(sprintf('%s::$embeddedObject::$multiEnum', User::class), $e->getPropertyPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\JmsSerializer\Enum\MappedClassMismatchException::class, $previous);
			$this->assertSame(RolesEnum::class, $previous->getMappedClassName());
			$this->assertSame(RoleEnum::class, $previous->getValueClassName());
		}
	}

	public function testDeserializeEnumInvalidValue(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{
				"single_enum": "%s"
			}', 'foo'), User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumInvalidValueEmbeddedObject(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{"embedded_object": {
				"single_enum": "%s"
			}}', 'foo'), User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('embedded_object.single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumWhenValueIsArray(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_enum": [1, 2, 3]
			}', User::class, 'json');

			$this->fail('Exception expected.');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertEquals([1, 2, 3], $previous->getValue());
		}
	}

	public function testDeserializeEnumWhenValueIsObject(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"single_enum": {"foo": "bar"}
			}', User::class, 'json');

			$this->fail('Exception expected.');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertEquals(['foo' => 'bar'], $previous->getValue());
		}
	}

	public function testDeserializeMultiEnumWithInvalidValueType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{
				"multi_enum": "%s"
			}', 'foo'), User::class, 'json');

			$this->fail('Exception expected.');

		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('multi_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertSame('foo', $previous->getValue());
		}
	}

	public function testSerializeEnumAsSingleEnumsArrayNotMappedSingleEnum(): void
	{
		$user = new User();
		$user->multiNoSingleEnumMapped = FooEnum::getMulti(FooEnum::FOO);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			$this->fail();
		} catch (\Consistence\Enum\NoSingleEnumSpecifiedException $e) {
			$this->assertSame(FooEnum::class, $e->getClass());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNotMappedSingleEnum(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{
				"multi_no_single_enum_mapped": %d
			}', FooEnum::FOO), User::class, 'json');
			$this->fail();
		} catch (\Consistence\Enum\NoSingleEnumSpecifiedException $e) {
			$this->assertSame(FooEnum::class, $e->getClass());
		}
	}

	public function testSerializeEnumAsSingleEnumsArrayNotMultiEnum(): void
	{
		$user = new User();
		$user->singleMappedAsMulti = RoleEnum::get(RoleEnum::ADMIN);
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\NotMultiEnumException $e) {
			$this->assertSame(RoleEnum::class, $e->getClassName());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNotMultiEnum(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{
				"single_mapped_as_multi": [
					"%s",
					"%s"
				]
			}', RoleEnum::ADMIN, RoleEnum::EMPLOYEE), User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\NotMultiEnumException $e) {
			$this->assertSame(RoleEnum::class, $e->getClassName());
		}
	}

	public function testDeserializeEnumAsSingleEnumsArrayNoArrayGiven(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"multi_enum_as_single_enums_array": "foo"
			}', User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertSame('multi_enum_as_single_enums_array', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\JmsSerializer\Enum\NotIterableValueException::class, $previous);
			$this->assertSame('foo', $previous->getValue());
		}
	}

	public function testDeserializeEnumFromXmlWithoutDeserializationType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf(
				'<?xml version="1.0" encoding="UTF-8"?>'
				. '<result>'
				. '<single_enum><![CDATA[admin]]></single_enum>'
				. '</result>',
				RoleEnum::ADMIN
			), User::class, 'xml');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('single_enum', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertInstanceOf(SimpleXMLElement::class, $previous->getValue());
		}
	}

	public function testDeserializeEnumWithWrongDeserializationType(): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize('{
				"type_enum_with_type": 1
			}', User::class, 'json');
			$this->fail();
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			$this->assertEquals('type_enum_with_type', $e->getFieldPath());
			$previous = $e->getPrevious();
			$this->assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			$this->assertSame('1', $previous->getValue());
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
