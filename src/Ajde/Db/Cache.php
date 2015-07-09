<?php


namespace Ajde\Db;

use Ajde\Object\Singleton;



class Cache extends Singleton
{
	protected $_cache = null;
	
	/**
	 * @return Ajde_Db_Cache
	 */
	public static function getInstance()
	{
    	static $instance;
    	return $instance === null ? $instance = new self : $instance;
	}	
}