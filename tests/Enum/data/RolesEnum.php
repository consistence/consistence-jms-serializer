<?php

declare(strict_types = 1);

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

	public static function getSingleEnumClass(): string
	{
		return RoleEnum::class;
	}

	/**
	 * Converts value representing a value from single Enum to MultiEnum counterpart
	 *
	 * @param string $singleEnumValue
	 * @return integer
	 */
	protected static function convertSingleEnumValueToValue($singleEnumValue): int
	{
		return ArrayType::getValue(self::$singleMultiMap, $singleEnumValue);
	}

	/**
	 * Converts value representing a value from MultiEnum to single Enum counterpart
	 *
	 * @param integer $value
	 * @return string
	 */
	protected static function convertValueToSingleEnumValue(int $value): string
	{
		return ArrayType::getKey(self::$singleMultiMap, $value);
	}

}
