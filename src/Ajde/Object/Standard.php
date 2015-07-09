<?php


namespace Ajde\Object;

use Ajde\Object\Magic;
use Ajde\Exception;



abstract class Standard extends Magic
{
	protected static $__pattern = self::OBJECT_PATTERN_STANDARD;

	public static function __getPattern()
	{
		return self::$__pattern;
	}

    /**
     * This can now be natively done since PHP 5.4
     * @see http://docs.php.net/manual/en/migration54.new-features.php
     *
     * @return Ajde_Object_Standard
     * @throws Ajde_Exception
     */
	public static function create()
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$className = get_called_class();
			return new $className();
		} else {
			// TODO:
			throw new Exception('Static method Ajde_Object_Standard::create() only available in PHP >= 5.3.0');
		}
	}
}