<?php


namespace Ajde\Document\Format;

use Ajde\Document;
use \Ajde;
use Ajde\Layout;



class Body extends Document
{
	protected $_cacheControl = self::CACHE_CONTROL_PRIVATE;
	protected $_maxAge = 0; // access
	
	public function render()
	{		
		Ajde::app()->getDocument()->setLayout(new Layout('empty'));		
		return parent::render();
	}
	
	public function getBody()
	{
		return $this->get('body');
	}
}