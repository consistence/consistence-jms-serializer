<?php

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\ArrayType\ArrayType;

class RolesEnum extends \Consistence\Enum\MultiEnum
{

	/** @var integer[] format: single Enum value (string) => MultiEnum value (integer) */
	private static $singleMultiMap = [
		RoleEnum::ADMIN => 1,
		RoleEnum::EMPLOYEE => 2,
		RoleEnum::USER => 4,
	];

	/**
	 * @return string
	 */
	public static function getSingleEnumClass()
	{
		return RoleEnum::class;
	}

	/**
	 * Converts value representing a value from single Enum to MultiEnum counterpart
	 *
	 * @param string $singleEnumValue
	 * @return integer
	 */
	protected static function convertSingleEnumValueToValue($singleEnumValue)
	{
		return ArrayType::getValue(self::$singleMultiMap, $singleEnumValue);
	}

	/**
	 * Converts value representing a value from MultiEnum to single Enum counterpart
	 *
	 * @param integer $value
	 * @return string
	 */
	protected static function convertValueToSingleEnumValue($value)
	{
		return ArrayType::getKey(self::$singleMultiMap, $value);
	}

}
