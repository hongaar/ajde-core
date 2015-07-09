<?php


namespace Ajde\Document\Format;

use Ajde\Document;
use \Ajde;
use Ajde\Layout;



class Js extends Document
{
	protected $_cacheControl = self::CACHE_CONTROL_PUBLIC;
	protected $_contentType = 'text/javascript';

	public function render()
	{
		Ajde::app()->getDocument()->setLayout(new Layout('empty'));		
		Ajde::app()->getResponse()->removeHeader('Set-Cookie');
		return parent::render();
	}
}