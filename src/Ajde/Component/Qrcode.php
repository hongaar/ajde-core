<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Resource\Qrcode as AjdeResourceQrcode;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Component\Exception;




class Qrcode extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'text' => 'html',
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
		case 'html':
			$qr = new AjdeResourceQrcode($this->attributes['text']);
			
			$controller = Controller::fromRoute(new Route('_core/component:qrcode'));
			$controller->setQrcode($qr);			
			return $controller->invoke();
			break;
		}
		// TODO:
		throw new Exception('Missing required attributes for component call');	
	}
}