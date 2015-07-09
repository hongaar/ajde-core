<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Resource\Image as AjdeResourceImage;
use Ajde\Component\String;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Component\Exception;




class Image extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'base64' => 'base64',
			'filename' => 'html',
			'output' => 'image'
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
		case 'base64':			
			$image = new AjdeResourceImage($this->attributes['filename']);
			$image->setWidth(issetor($this->attributes['width']));
			$image->setHeight(issetor($this->attributes['height']));
			$image->setCrop(String::toBoolean(issetor($this->attributes['crop'], true)));
						
			$controller = Controller::fromRoute(new Route('_core/component:imageBase64'));
			$controller->setImage($image);
			$controller->setWidth(issetor($this->attributes['width'], null));
			$controller->setHeight(issetor($this->attributes['height'], null));
			$controller->setExtraClass(issetor($this->attributes['class'], ''));
					
			return $controller->invoke();
			break;
		case 'html':
			return self::getImageTag(
                $this->attributes['filename'],
                issetor($this->attributes['width']),
                issetor($this->attributes['height']),
                String::toBoolean(issetor($this->attributes['crop'], true)),
                issetor($this->attributes['class'], ''),
                issetor($this->attributes['lazy'], false),
                issetor($this->attributes['absoluteUrl'], false)
            );
			break;
		case 'image':
			return false;
			break;
		}
		// TODO:
		throw new Exception('Missing required attributes for component call');	
	}

    public static function getImageTag($filename, $width = null, $height = null, $crop = true, $class = '', $lazy = false, $absoluteUrl = false)
    {
        $image = new AjdeResourceImage($filename);
        $image->setWidth($width);
        $image->setHeight($height);
        $image->setCrop($crop);

        $controller = Controller::fromRoute(new Route('_core/component:image'));
        $controller->setImage($image);
        $controller->setExtraClass($class);
        $controller->setLazy($lazy);
        $controller->setAbsoluteUrl($absoluteUrl);

        return $controller->invoke();
    }
}