<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

class MappedClassMismatchException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var string */
	private $mappedClassName;

	/** @var string */
	private $valueClassName;

	public function __construct(string $mappedClassName, string $valueClassName, \Throwable $previous = null)
	{
		parent::__construct(sprintf(
			'Class of given value "%s" does not match mapped %s<%s>',
			$valueClassName,
			EnumSerializerHandler::TYPE_ENUM,
			$mappedClassName
		), $previous);
		$this->mappedClassName = $mappedClassName;
		$this->valueClassName = $valueClassName;
	}

	public function getMappedClassName(): string
	{
		return $this->mappedClassName;
	}

	public function getValueClassName(): string
	{
		return $this->valueClassName;
	}

}
