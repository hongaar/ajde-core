<?php


namespace Ajde\Session;

use Ajde\Object\Static;
use Ajde\Session;
use Ajde\Cache;



class Flash extends Static
{	
	public static function set($key, $value)
	{
		$session = new Session('AC.Flash');
		$session->set($key, $value);
	}
	
	public static function get($key)
	{        
		$session = new Session('AC.Flash');
		if ($session->has($key)) {
            
            // Disable the cache, as getting a flashed string means outputting some message to the user?
            Cache::getInstance()->disable();
        
			return $session->getOnce($key);
		} else {
			return false;
		}
	}
	
	public static function alert($message)
	{
		self::set('alert', $message);
	}
}