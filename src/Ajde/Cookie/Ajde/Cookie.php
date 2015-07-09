<?php


namespace Ajde;

use Ajde\Object\Standard;
use Exception as Exception;
use Ajde\Dump;
use Ajde\Model;
use Ajde\Exception as AjdeException;
use Ajde\Component\String;
use Config;



class Cookie extends Standard
{
	protected $_namespace = null;
	protected $_lifetime = 90;
	protected $_secure = false;
	
	public function __construct($namespace = 'default', $secure = false)
	{
		$this->_namespace = $namespace;
		$this->_secure = $secure;
		if (isset($_COOKIE[$this->_namespace])) {
			$this->_data = $this->reader();
		}
	}
	
	public function destroy()
	{
		$this->_setcookie('', time() - 3600);
		$this->reset(); 
	}
	
	public function setModel($name, $object)
	{
		$this->set($name, serialize($object));	
	}
	
	public function getModel($name)
	{
		// If during the session class definitions has changed, this will throw an exception.
		try {
			return unserialize($this->get($name));
		} catch(Exception $e) {
			Dump::warn('Model definition changed during cookie period');
			return false;
		}
	}
	
	public function setLifetime($days)
	{
		$this->_lifetime = $days;
	}
		
	public function set($key, $value)
	{
		parent::set($key, $value);
		if ($value instanceof Model) {
			// TODO:
			throw new AjdeException('It is not allowed to store a Model directly in a cookie, use Ajde_Cookie::setModel() instead.');
		}
		$this->_setcookie($this->writer(), time() + (60 * 60 * 24 * $this->_lifetime));
	}
	
	protected function reader()
	{
		if ($this->_secure) {
			return @unserialize(String::decrypt($_COOKIE[$this->_namespace]));
		} else {
			return @unserialize($_COOKIE[$this->_namespace]);
		}
	}
	
	protected function writer()
	{
		if ($this->_secure) {
			return String::encrypt(serialize($this->values()));
		} else {
			return serialize($this->values());
		}
	}
	
	protected function _setcookie($value, $lifetime)
	{
		$path		= Config::get('site_path');
		$domain		= Config::get('cookieDomain');
		$secure		= Config::get('cookieSecure');
		$httponly	= Config::get('cookieHttponly');
		setcookie($this->_namespace, $value, $lifetime, $path, $domain, $secure, $httponly);
	}
}