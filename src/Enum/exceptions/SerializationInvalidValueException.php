<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

class SerializationInvalidValueException extends \Consistence\PhpException
{

	/** @var string */
	private $propertyPath;

	public function __construct(string $fieldPath, \Throwable $exception)
	{
		parent::__construct(sprintf('Invalid value in property %s: %s', $fieldPath, $exception->getMessage()), $exception);
		$this->propertyPath = $fieldPath;
	}

	public function getPropertyPath(): string
	{
		return $this->propertyPath;
	}

}
