<?php
namespace Ajde\Object;

use Ajde\Object\Singleton\SingletonInterface;


abstract class Singleton extends Magic implements SingletonInterface
{
    protected static $__pattern = self::OBJECT_PATTERN_SINGLETON;

    public static function __getPattern()
    {
        return self::$__pattern;
    }

    // Do not allow an explicit call of the constructor
    protected function __construct()
    {
    }

    // Do not allow the clone operation
    private final function __clone()
    {
    }
}

