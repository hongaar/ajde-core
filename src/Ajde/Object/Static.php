<?php


namespace Ajde\Object;

use Ajde\Object;



abstract class Static extends Object
{
	protected static $__pattern = self::OBJECT_PATTERN_STATIC;

	public static function __getPattern()
	{
		return self::$__pattern;
	}
	
    // Do not allow an explicit call of the constructor
    final protected function __construct() {}

    // Do not allow the clone operation:
    final protected function __clone() {}
	
}