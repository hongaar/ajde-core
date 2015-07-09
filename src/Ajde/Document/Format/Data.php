<?php


namespace Ajde\Document\Format;

use Ajde\Document;
use \Ajde;



class Data extends Document
{	
	protected $_cacheControl = self::CACHE_CONTROL_PUBLIC;
	protected $_maxAge = 2678400; // 1 month

	public function render()
	{
		Ajde::app()->getResponse()->removeHeader('Set-Cookie');
		// Get the controller to output the right headers and body
		return parent::getBody();
	}
}