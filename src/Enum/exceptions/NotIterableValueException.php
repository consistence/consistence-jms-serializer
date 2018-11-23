<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

use Consistence\Type\Type;

class NotIterableValueException extends \Consistence\PhpException
{

	/** @var mixed */
	private $value;

	/**
	 * @param mixed $value
	 * @param \Throwable|null $previous
	 */
	public function __construct($value, ?\Throwable $previous = null)
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
