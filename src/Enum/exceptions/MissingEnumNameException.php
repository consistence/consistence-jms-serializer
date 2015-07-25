<?php

namespace Consistence\JmsSerializer\Enum;

class MissingEnumNameException extends \Consistence\PhpException implements \Consistence\JmsSerializer\Enum\Exception
{

	public function __construct(\Exception $previous = null)
	{
		parent::__construct('Missing enum class name', $previous);
	}

}
