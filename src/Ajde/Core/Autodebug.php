<?php


namespace Ajde\Core;

use Ajde\Object\Singleton;
use Ajde\Model;
use Ajde\User;
use Config;



class Autodebug extends Singleton
{	
	public static function getInstance()
	{
		static $instance;
		return $instance === null ? $instance = new self : $instance;
	}
	 
	static public function __bootstrap()
	{
		Model::register('user');
		if ( ($user = User::getLoggedIn()) && $user->getDebug()) {
			$config = Config::getInstance();
			$config->debug = true;

            if (!in_array('Debugger', $config->documentProcessors['html'])) {
                $config->documentProcessors['html'][] = 'Debugger';
            }
		}
		return true;
	}
}