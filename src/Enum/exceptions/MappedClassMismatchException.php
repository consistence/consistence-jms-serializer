<?php

namespace Consistence\JmsSerializer\Enum;

class MappedClassMismatchException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var string */
	private $mappedClassName;

	/** @var string */
	private $valueClassName;

	/**
	 * @param string $mappedClassName
	 * @param string $valueClassName
	 * @param \Exception|null $previous
	 */
	public function __construct($mappedClassName, $valueClassName, \Exception $previous = null)
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

	/**
	 * @return string
	 */
	public function getMappedClassName()
	{
		return $this->mappedClassName;
	}

	/**
	 * @return string
	 */
	public function getValueClassName()
	{
		return $this->valueClassName;
	}

}
