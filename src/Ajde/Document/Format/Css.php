<?php


namespace Ajde\Document\Format;

use Ajde\Document;
use \Ajde;
use Ajde\Layout;



class Css extends Document
{
	protected $_cacheControl = self::CACHE_CONTROL_PUBLIC;
	protected $_contentType = 'text/css';

	public function render()
	{
		Ajde::app()->getDocument()->setLayout(new Layout('empty'));
		Ajde::app()->getResponse()->removeHeader('Set-Cookie');
		if (Ajde::app()->getRequest()->getRoute()->getAction() == 'resourceCompressed') {
			$this->registerDocumentProcessor('css', 'compressor');
		} else {
			$this->registerDocumentProcessor('css');
		}
		return parent::render();		
	}	
}