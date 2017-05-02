<?php

declare(strict_types = 1);

namespace Consistence\JmsSerializer\Enum;

class MissingEnumNameException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	public function __construct(\Throwable $previous = null)
	{
		parent::__construct('Missing enum class name', $previous);
	}

}
