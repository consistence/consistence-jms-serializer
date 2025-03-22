<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use Closure;
use Consistence\Type\Type;
use Generator;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Visitor\Factory\XmlSerializationVisitorFactory;
use PHPUnit\Framework\Assert;
use SimpleXMLElement;
use stdClass;

class EnumSerializerHandlerTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @return mixed[][]|\Generator
	 */
	public function serializeEnumPropertyDataProvider(): Generator
	{
		yield 'single enum' => (function (): array {
			$role = RoleEnum::get(RoleEnum::ADMIN);

			$user = new User();
			$user->singleEnum = $role;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"single_enum":"admin"',
				'serializedPropertyInXml' => '<single_enum><![CDATA[admin]]></single_enum>',
				'deserializationUserAssertsCallback' => function (User $user) use ($role): void {
					Assert::assertInstanceOf(User::class, $user);
					Assert::assertSame($role, $user->singleEnum);
				},
				'supportedXmlDeserialization' => false,
			];
		})();

		yield 'single enum with type' => (function (): array {
			$role = RoleEnum::get(RoleEnum::ADMIN);

			$user = new User();
			$user->singleEnumWithType = $role;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"single_enum_with_type":"admin"',
				'serializedPropertyInXml' => '<single_enum_with_type><![CDATA[admin]]></single_enum_with_type>',
				'deserializationUserAssertsCallback' => function (User $user) use ($role): void {
					Assert::assertInstanceOf(User::class, $user);
					Assert::assertSame($role, $user->singleEnumWithType);
				},
				'supportedXmlDeserialization' => true,
			];
		})();

		yield 'multi enum' => (function (): array {
			$roles = RolesEnum::getMultiByEnums([
				RoleEnum::get(RoleEnum::ADMIN),
				RoleEnum::get(RoleEnum::EMPLOYEE),
			]);

			$user = new User();
			$user->multiEnum = $roles;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"multi_enum":3',
				'serializedPropertyInXml' => '<multi_enum>3</multi_enum>',
				'deserializationUserAssertsCallback' => function (User $user) use ($roles): void {
					Assert::assertInstanceOf(User::class, $user);
					Assert::assertSame($roles, $user->multiEnum);
				},
				'supportedXmlDeserialization' => false,
			];
		})();

		yield 'array of single enums' => (function (): array {
			$roles = [
				RoleEnum::get(RoleEnum::ADMIN),
				RoleEnum::get(RoleEnum::EMPLOYEE),
			];

			$user = new User();
			$user->arrayOfSingleEnums = $roles;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"array_of_single_enums":["admin","employee"]',
				'serializedPropertyInXml' => '<array_of_single_enums>'
					. '<entry><![CDATA[admin]]></entry>'
					. '<entry><![CDATA[employee]]></entry>'
					. '</array_of_single_enums>',
				'deserializationUserAssertsCallback' => function (User $user) use ($roles): void {
					foreach ($roles as $expectedRole) {
						Assert::assertContains($expectedRole, $user->arrayOfSingleEnums);
					}
					Assert::assertCount(2, $user->arrayOfSingleEnums);
				},
				'supportedXmlDeserialization' => false,
			];
		})();

		yield 'multi enum as single enums array' => (function (): array {
			$roles = RolesEnum::getMultiByEnums([
				RoleEnum::get(RoleEnum::ADMIN),
				RoleEnum::get(RoleEnum::EMPLOYEE),
			]);

			$user = new User();
			$user->multiEnumAsSingleEnumsArray = $roles;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"multi_enum_as_single_enums_array":["employee","admin"]',
				'serializedPropertyInXml' => '<multi_enum_as_single_enums_array>'
					. '<entry><![CDATA[employee]]></entry>'
					. '<entry><![CDATA[admin]]></entry>'
					. '</multi_enum_as_single_enums_array>',
				'deserializationUserAssertsCallback' => function (User $user) use ($roles): void {
					Assert::assertSame($roles, $user->multiEnumAsSingleEnumsArray);
				},
				'supportedXmlDeserialization' => false,
			];
		})();

		yield 'multi enum as single enums array with type' => (function (): array {
			$roles = RolesEnum::getMultiByEnums([
				RoleEnum::get(RoleEnum::ADMIN),
				RoleEnum::get(RoleEnum::EMPLOYEE),
			]);

			$user = new User();
			$user->multiEnumAsSingleEnumsArrayWithType = $roles;

			return [
				'user' => $user,
				'serializedPropertyInJson' => '"multi_enum_as_single_enums_array_with_type":["employee","admin"]',
				'serializedPropertyInXml' => '<multi_enum_as_single_enums_array_with_type>'
					. '<entry><![CDATA[employee]]></entry>'
					. '<entry><![CDATA[admin]]></entry>'
					. '</multi_enum_as_single_enums_array_with_type>',
				'deserializationUserAssertsCallback' => function (User $user) use ($roles): void {
					Assert::assertSame($roles, $user->multiEnumAsSingleEnumsArrayWithType);
				},
				'supportedXmlDeserialization' => true,
			];
		})();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function serializeEnumDataProvider(): Generator
	{
		foreach ($this->serializeEnumPropertyDataProvider() as $caseName => $caseData) {
			yield $caseName . ' (JSON)' => [
				'format' => 'json',
				'user' => $caseData['user'],
				'serializedProperty' => $caseData['serializedPropertyInJson'],
			];
			yield $caseName . ' (XML)' => [
				'format' => 'xml',
				'user' => $caseData['user'],
				'serializedProperty' => $caseData['serializedPropertyInXml'],
			];
		}
	}

	/**
	 * @dataProvider serializeEnumDataProvider
	 *
	 * @param string $format
	 * @param \Consistence\JmsSerializer\Enum\User $user
	 * @param string $serializedProperty
	 */
	public function testSerializeEnum(string $format, User $user, string $serializedProperty): void
	{
		$serializer = $this->getSerializer();
		$serializedOutput = $serializer->serialize($user, $format);
		Assert::assertStringContainsString($serializedProperty, $serializedOutput);
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function typeDataProvider(): Generator
	{
		yield 'integer' => [
			'value' => TypeEnum::INTEGER,
			'serializedValueInJson' => '1',
			'serializedValueInXml' => '1',
		];
		yield 'string' => [
			'value' => TypeEnum::STRING,
			'serializedValueInJson' => '"foo"',
			'serializedValueInXml' => '<![CDATA[foo]]>',
		];
		yield 'float' => [
			'value' => TypeEnum::FLOAT,
			'serializedValueInJson' => '2.5',
			'serializedValueInXml' => '2.5',
		];
		yield 'boolean' => [
			'value' => TypeEnum::BOOLEAN,
			'serializedValueInJson' => 'true',
			'serializedValueInXml' => 'true',
		];
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function jsonTypeDataProvider(): Generator
	{
		foreach ($this->typeDataProvider() as $caseName => $caseData) {
			yield $caseName => [
				'value' => $caseData['value'],
				'serializedValue' => $caseData['serializedValueInJson'],
			];
		}
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
		foreach ($this->typeDataProvider() as $caseName => $caseData) {
			yield $caseName => [
				'value' => $caseData['value'],
				'serializedValue' => $caseData['serializedValueInXml'],
			];
		}
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

	/**
	 * @return mixed[][]|\Generator
	 */
	public function deserializeEnumFromJsonDataProvider(): Generator
	{
		foreach ($this->serializeEnumPropertyDataProvider() as $caseName => $caseData) {
			yield $caseName => [
				'serializedProperty' => $caseData['serializedPropertyInJson'],
				'userAssertsCallback' => $caseData['deserializationUserAssertsCallback'],
			];
		}
	}

	/**
	 * @dataProvider deserializeEnumFromJsonDataProvider
	 *
	 * @param string $serializedProperty
	 * @param \Closure $userAssertsCallback
	 */
	public function testDeserializeEnumFromJson(string $serializedProperty, Closure $userAssertsCallback): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf('{%s}', $serializedProperty), User::class, 'json');
		Assert::assertInstanceOf(User::class, $user);
		$userAssertsCallback($user);
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function deserializeEnumFromXmlDataProvider(): Generator
	{
		foreach ($this->serializeEnumPropertyDataProvider() as $caseName => $caseData) {
			if (!$caseData['supportedXmlDeserialization']) {
				continue;
			}

			yield $caseName => [
				'serializedProperty' => $caseData['serializedPropertyInXml'],
				'userAssertsCallback' => $caseData['deserializationUserAssertsCallback'],
			];
		}
	}

	/**
	 * @dataProvider deserializeEnumFromXmlDataProvider
	 *
	 * @param string $serializedProperty
	 * @param \Closure $userAssertsCallback
	 */
	public function testDeserializeEnumFromXml(string $serializedProperty, Closure $userAssertsCallback): void
	{
		$serializer = $this->getSerializer();
		$user = $serializer->deserialize(sprintf(
			'<?xml version="1.0" encoding="UTF-8"?>'
			. '<result>'
			. '%s'
			. '</result>',
			$serializedProperty
		), User::class, 'xml');
		Assert::assertInstanceOf(User::class, $user);
		$userAssertsCallback($user);
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

	/**
	 * @return mixed[][]|\Generator
	 */
	public function serializeEnumInvalidValueDataProvider(): Generator
	{
		yield 'single enum passed to multi enum' => [
			'user' => (function (): User {
				$user = new User();
				$user->multiEnum = RoleEnum::get(RoleEnum::ADMIN);

				return $user;
			})(),
			'propertyPath' => sprintf('%s::$multiEnum', User::class),
			'mappedClassName' => RolesEnum::class,
			'valueClassName' => RoleEnum::class,
		];

		yield 'single enum passed to multi enum defined in embedded object' => [
			'user' => (function (): User {
				$embeddedUser = new User();
				$embeddedUser->multiEnum = RoleEnum::get(RoleEnum::ADMIN);

				$user = new User();
				$user->embeddedObject = $embeddedUser;

				return $user;
			})(),
			'propertyPath' => sprintf('%s::$embeddedObject::$multiEnum', User::class),
			'mappedClassName' => RolesEnum::class,
			'valueClassName' => RoleEnum::class,
		];
	}

	/**
	 * @dataProvider serializeEnumInvalidValueDataProvider
	 *
	 * @param \Consistence\JmsSerializer\Enum\User $user
	 * @param string $propertyPath
	 * @param string $mappedClassName
	 * @param string $valueClassName
	 */
	public function testSerializeEnumInvalidValue(
		User $user,
		string $propertyPath,
		string $mappedClassName,
		string $valueClassName
	): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->serialize($user, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\SerializationInvalidValueException $e) {
			Assert::assertSame($propertyPath, $e->getPropertyPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\JmsSerializer\Enum\MappedClassMismatchException::class, $previous);
			Assert::assertSame($mappedClassName, $previous->getMappedClassName());
			Assert::assertSame($valueClassName, $previous->getValueClassName());
		}
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function deserializeEnumInvalidValueDataProvider(): Generator
	{
		yield 'string instead of single enum' => [
			'serializedProperty' => '"single_enum": "foo"',
			'field' => 'single_enum',
			'value' => 'foo',
		];
		yield 'string instead of single enum in embedded object' => [
			'serializedProperty' => '"embedded_object": {
				"single_enum": "foo"
			}',
			'field' => 'embedded_object.single_enum',
			'value' => 'foo',
		];
		yield 'array instead of single enum' => [
			'serializedProperty' => '"single_enum": [1, 2, 3]',
			'field' => 'single_enum',
			'value' => [1, 2, 3],
		];
		yield 'object instead of single enum' => [
			'serializedProperty' => '"single_enum": {"foo": "bar"}',
			'field' => 'single_enum',
			'value' => ['foo' => 'bar'],
		];
		yield 'string instead of multi enum' => [
			'serializedProperty' => '"multi_enum": "foo"',
			'field' => 'multi_enum',
			'value' => 'foo',
		];
		yield 'integer instead of string with explicitly mapped type' => [
			'serializedProperty' => '"type_enum_with_type": 1',
			'field' => 'type_enum_with_type',
			'value' => '1',
		];
	}

	/**
	 * @dataProvider deserializeEnumInvalidValueDataProvider
	 *
	 * @param string $serializedProperty
	 * @param string $field
	 * @param mixed $value
	 */
	public function testDeserializeEnumInvalidValue(
		string $serializedProperty,
		string $field,
		$value
	): void
	{
		$serializer = $this->getSerializer();

		try {
			$serializer->deserialize(sprintf('{%s}', $serializedProperty), User::class, 'json');
			Assert::fail('Exception expected');
		} catch (\Consistence\JmsSerializer\Enum\DeserializationInvalidValueException $e) {
			Assert::assertSame($field, $e->getFieldPath());
			$previous = $e->getPrevious();
			Assert::assertInstanceOf(\Consistence\Enum\InvalidEnumValueException::class, $previous);
			Assert::assertSame($value, $previous->getValue());
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

	private function getSerializer(): SerializerInterface
	{
		$xmlSerializationVisitorFactory = new XmlSerializationVisitorFactory();
		$xmlSerializationVisitorFactory->setFormatOutput(false);

		return SerializerBuilder::create()
			->addDefaultDeserializationVisitors()
			->addDefaultSerializationVisitors()
			->setSerializationVisitor('xml', $xmlSerializationVisitorFactory)
			->configureHandlers(function (HandlerRegistry $registry): void {
				$registry->registerSubscribingHandler(new EnumSerializerHandler());
			})
			->build();
	}

}
