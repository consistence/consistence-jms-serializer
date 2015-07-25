<?php

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\Type;

class NotIterableValueException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var mixed */
	private $value;

	/**
	 * @param mixed $value
	 * @param \Exception|null $previous
	 */
	public function __construct($value, \Exception $previous = null)
	{
		parent::__construct(sprintf('Value of type %s is not iterable', Type::getType($value)), $previous);
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

}
