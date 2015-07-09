<?php


namespace Ajde\Shop\Cart;

use Ajde\Object\Singleton;
use Ajde\Model;
use Ajde\User;
use CartModel;
use Ajde\Session\Flash;



class Merge extends Singleton
{	
	public static function getInstance()
	{
		static $instance;
		return $instance === null ? $instance = new self : $instance;
	}
	 
	static public function __bootstrap()
	{
		self::mergeClientToUser();
		return true;
	}
	
	static public function mergeClientToUser()
	{
		Model::register('user');
		Model::register('shop');
		
		if ($user = User::getLoggedIn()) {
			
			// Do we have a saved client cart?
			$clientCart = new CartModel();			
			if ($clientCart->loadByClient()) {
				
				// Do we have a saved cart for logged in user?
				$userCart = new CartModel();				
				if ($userCart->loadByUser($user) === false) {
					$userCart->user = $user->getPK();
					$userCart->insert();
				}
				
				if ($userCart->hasItems()) {
					// Set alert message
					Flash::alert(__("Your items are still in the shopping cart"));
				}
				
				// Merge items
				foreach($clientCart->getItems() as $item) {				
					/* @var $item Ajde_Shop_Cart_Item */
					$userCart->addItem($item->getEntity(), null, $item->getQty());
				}
				
				// And delete client
				$clientCart->delete();				
			}
		}
	}
	
	static public function mergeUserToClient()
	{
		Model::register('user');
		Model::register('shop');
		
		if ($user = User::getLoggedIn()) {	
			
			// Do we have a saved cart for logged in user?
			$userCart = new CartModel();			
			if ($userCart->loadByUser($user)) {				
							
				// Do we have a saved cart for client?				
				$clientCart = new CartModel();
				if ($clientCart->loadByClient() === false) {
					$clientCart->client = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
					$clientCart->insert();
				}				
				
				foreach($userCart->getItems() as $item) {
					/* @var $item Ajde_Shop_Cart_Item */
					$clientCart->addItem($item->getEntity(), null, $item->getQty());
				}
				
				$userCart->delete();
			}
		}
	}
}