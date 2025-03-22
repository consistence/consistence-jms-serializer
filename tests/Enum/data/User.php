<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use JMS\Serializer\Annotation as JMS;

class User
{

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RoleEnum>")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum
	 */
	public $singleEnum;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RoleEnum, string>")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum
	 */
	public $singleEnumWithType;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $typeEnum;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RolesEnum>")
	 * @var \Consistence\JmsSerializer\Enum\RolesEnum
	 */
	public $multiEnum;

	/**
	 * @JMS\Type("array<enum<Consistence\JmsSerializer\Enum\RoleEnum>>")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum[]
	 */
	public $arrayOfSingleEnums;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RolesEnum, as_single>")
	 * @var \Consistence\JmsSerializer\Enum\RolesEnum
	 */
	public $multiEnumAsSingleEnumsArray;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RolesEnum, as_single, string>")
	 * @var \Consistence\JmsSerializer\Enum\RolesEnum
	 */
	public $multiEnumAsSingleEnumsArrayWithType;

	/**
	 * @JMS\Type("enum")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum
	 */
	public $missingEnumName;

	/**
	 * @JMS\Type("enum<stdClass>")
	 * @var \Consistence\JmsSerializer\Enum\RolesEnum
	 */
	public $invalidEnumClass;

	/**
	 * @JMS\Type("Consistence\JmsSerializer\Enum\User")
	 * @var \Consistence\JmsSerializer\Enum\User
	 */
	public $embeddedObject;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\FooEnum, as_single>")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum
	 */
	public $multiNoSingleEnumMapped;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\RoleEnum, as_single>")
	 * @var \Consistence\JmsSerializer\Enum\RoleEnum
	 */
	public $singleMappedAsMulti;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum, string>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $typeEnumWithType;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum, string>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $string;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum, int>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $int;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum, bool>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $bool;

	/**
	 * @JMS\Type("enum<Consistence\JmsSerializer\Enum\TypeEnum, float>")
	 * @var \Consistence\JmsSerializer\Enum\TypeEnum
	 */
	public $float;

}
