<?php

namespace Consistence\JmsSerializer\Enum;

class DeserializationInvalidValueException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	/** @var string */
	private $fieldPath;

	/**
	 * @param string $fieldPath
	 * @param \Exception $exception
	 */
	public function __construct($fieldPath, \Exception $exception)
	{
		parent::__construct(sprintf('Invalid value in field %s: %s', $fieldPath, $exception->getMessage()), $exception);
		$this->fieldPath = $fieldPath;
	}

	/**
	 * @return string
	 */
	public function getFieldPath()
	{
		return $this->fieldPath;
	}

}
