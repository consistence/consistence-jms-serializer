<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

class DeserializationInvalidValueException extends \Consistence\PhpException
{

	/** @var string */
	private $fieldPath;

	public function __construct(string $fieldPath, \Throwable $exception)
	{
		parent::__construct(sprintf('Invalid value in field %s: %s', $fieldPath, $exception->getMessage()), $exception);
		$this->fieldPath = $fieldPath;
	}

	public function getFieldPath(): string
	{
		return $this->fieldPath;
	}

}
