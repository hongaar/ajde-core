<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Resource\Image;
use Ajde\Component\String;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Component\Exception;




class Embed extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'url' => 'render'
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
		case 'render':			
			$image = new Image($this->attributes['filename']);
			$image->setWidth($this->attributes['width']);
			$image->setHeight($this->attributes['height']);
			$image->setCrop(String::toBoolean($this->attributes['crop']));
						
			$controller = Controller::fromRoute(new Route('_core/component:imageBase64'));
			$controller->setImage($image);
			$controller->setWidth(issetor($this->attributes['width'], null));
			$controller->setHeight(issetor($this->attributes['height'], null));
			$controller->setExtraClass(issetor($this->attributes['class'], ''));
					
			return $controller->invoke();
			break;
		}
		// TODO:
		throw new Exception('Missing required attributes for component call');	
	}
}