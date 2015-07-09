<?php


namespace Ajde\User;

use Ajde\Object\Singleton;
use Ajde\Model;
use Ajde\User;
use UserModel;



class Autologon extends Singleton
{	
	public static function getInstance()
	{
		static $instance;
		return $instance === null ? $instance = new self : $instance;
	}
	 
	static public function __bootstrap()
	{
		Model::register('user');
		if (User::getLoggedIn()) {
			return true;
		}		
		$user = new UserModel();
		$user->verifyCookie(false);
		return true;
	}
}