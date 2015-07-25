<?php

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
	public $embededObject;

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

}
