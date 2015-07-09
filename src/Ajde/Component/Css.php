<?php 

namespace Ajde\Component;

use Ajde\Component\Resource as AjdeComponentResource;
use Ajde\Template\Parser;
use Ajde\Resource\Local;
use Ajde\Resource\GWebFont;
use Ajde\Resource\Remote;
use Ajde\Resource as AjdeResource;
use Ajde\Document\Format\Html;




class Css extends AjdeComponentResource
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'action' => 'local',
			'filename' => 'public',
			'url' => 'remote',
			'fontFamily' => 'font'
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
			case 'local':
				$this->requireResource(
					Local::TYPE_STYLESHEET,
					$this->attributes['action'],
					issetor($this->attributes['format'], null),
					issetor($this->attributes['base'], null),
					issetor($this->attributes['position'], null),
					issetor($this->attributes['arguments'], '')
				);
				break;
			case 'public':
				$this->requirePublicResource(
					Local::TYPE_STYLESHEET,
					$this->attributes['filename'],
					issetor($this->attributes['position'], null),
					issetor($this->attributes['arguments'], '')
				);
				break;
            case 'remote':
                $this->requireRemoteResource(
                    Local::TYPE_STYLESHEET,
                    $this->attributes['url'],
                    issetor($this->attributes['position'], null),
                    issetor($this->attributes['arguments'], '')
                );
                break;
			case 'font':
				$url = GWebFont::getUrl(
					$this->attributes['fontFamily'],
					issetor($this->attributes['fontWeight'], array(400)),
					issetor($this->attributes['fontSubset'], array('latin'))
				);
				$resource = new Remote(AjdeResource::TYPE_STYLESHEET, $url);
				$this->getParser()->getDocument()->addResource($resource, Html::RESOURCE_POSITION_TOP);
				break;
		}		
	}
}