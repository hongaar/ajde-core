<?php


namespace Ajde\Document\Format;

use Ajde\Document;
use Ajde\Cache;
use \Ajde;
use Ajde\Layout;
use Config;
use Ajde\Dump;



class Json extends Document
{
	protected $_cacheControl = self::CACHE_CONTROL_NOCACHE;
	protected $_contentType = 'application/json';
	protected $_maxAge = 0; // access

	public function render()
	{
		Cache::getInstance()->disable();
		Ajde::app()->getDocument()->setLayout(new Layout('empty'));		
		return parent::render();
	}
	
	public function getBody()
	{
		$body = json_encode($this->get('body'));
		if (Config::get('debug')) {
			if (Dump::getAll()) {
				foreach(Dump::getAll() as $source => $var) {
					//if ($var[1] === true) { $expand = true; }
					$body .= "<pre class='xdebug-var-dump'>" . var_export($var[0], true) . "</pre>";
				}
			}
		}
		return $body;
	}
}