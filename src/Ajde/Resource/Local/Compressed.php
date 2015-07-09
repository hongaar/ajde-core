<?php


namespace Ajde\Resource\Local;

use Ajde\Resource;
use Ajde\Core\Exception\Deprecated;
use Ajde\Session;
use Ajde\Resource\Local\Compressed as AjdeResourceLocalCompressed;
use Config;



class Compressed extends Resource
{
	public function  __construct($type, $filename)
	{
		$this->setFilename($filename);
		parent::__construct($type);		
	}

	/**
	 *
	 * @param string $hash
	 * @return Ajde_Resource
	 */
	public static function fromHash($hash)
	{
		// TODO:
		throw new Deprecated();
		$session = new Session('AC.Resource');
		return $session->get($hash);
	}
	
	public static function fromFingerprint($type, $fingerprint)
	{
		$array = self::decodeFingerprint($fingerprint);
		extract($array);
		return new AjdeResourceLocalCompressed($type, $f);
	}
	
	public function getFingerprint()
	{
		$array = array('f' => $this->getFilename());
		return $this->encodeFingerprint($array);
	}

	public function getLinkUrl()
	{		
		//$hash = md5(serialize($this));
		//$session = new Ajde_Session('AC.Resource');
		//$session->set($hash, $this);
		
		//$url = '_core/component:resourceCompressed/' . $this->getType() . '/' . $hash . '/';
		$url = '_core/component:resourceCompressed/' . urlencode($this->getFingerprint()) . '.' . $this->getType();
		
		if (Config::get('debug') === true)
		{
			$url .= '?file=' . str_replace(array('%2F', '%5C'), ':', urlencode($this->getFilename()));
		}
		return $url;
	}

	public function getFilename() {
		return $this->get('filename');
	}
}