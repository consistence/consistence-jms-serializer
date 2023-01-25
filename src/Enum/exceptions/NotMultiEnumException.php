<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

class NotMultiEnumException extends \Consistence\PhpException
{

	/** @var string */
	private $className;

	public function __construct(string $className, ?\Throwable $previous = null)
	{
		parent::__construct(sprintf('Class "%s" is not a MultiEnum', $className), $previous);
		$this->className = $className;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

}
