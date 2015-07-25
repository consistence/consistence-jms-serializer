<?php

namespace Consistence\JmsSerializer\Enum;

class NotEnumException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var string */
	private $className;

	/**
	 * @param string $className
	 * @param \Exception|null $previous
	 */
	public function __construct($className, \Exception $previous = null)
	{
		parent::__construct(sprintf('Class "%s" is not an Enum', $className), $previous);
		$this->className = $className;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

}
