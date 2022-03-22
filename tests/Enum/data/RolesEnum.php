<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\ArrayType\ArrayType;

class RolesEnum extends \Consistence\Enum\MultiEnum
{

	/** @var int[] format: single Enum value (string) => MultiEnum value (int) */
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
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 *
	 * @param string $singleEnumValue
	 * @return int
	 */
	protected static function convertSingleEnumValueToValue($singleEnumValue): int
	{
		return ArrayType::getValue(self::$singleMultiMap, $singleEnumValue);
	}

	/**
	 * Converts value representing a value from MultiEnum to single Enum counterpart
	 *
	 * @param int $value
	 * @return string
	 */
	protected static function convertValueToSingleEnumValue(int $value): string
	{
		return ArrayType::getKey(self::$singleMultiMap, $value);
	}

}
