<?php

namespace Consistence\JmsSerializer\Enum;

class SerializationInvalidValueException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var string */
	private $propertyPath;

	/**
	 * @param string $fieldPath
	 * @param \Exception $exception
	 */
	public function __construct($fieldPath, \Exception $exception)
	{
		parent::__construct(sprintf('Invalid value in property %s: %s', $fieldPath, $exception->getMessage()), $exception);
		$this->propertyPath = $fieldPath;
	}

	/**
	 * @return string
	 */
	public function getPropertyPath()
	{
		return $this->propertyPath;
	}

}
