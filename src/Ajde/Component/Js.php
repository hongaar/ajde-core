<?php 

namespace Ajde\Component;

use Ajde\Component\Resource as AjdeComponentResource;
use Ajde\Template\Parser;
use Ajde\Resource\Local;
use Ajde\Resource\JsLibrary;
use Ajde\Resource\Remote;
use Ajde\Resource as AjdeResource;
use Ajde\Document\Format\Html;




class Js extends AjdeComponentResource
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	public function process()
	{
//		TODO: check for required attributes
//		if (!array_key_exists('library', $this->attributes) || !array_key_exists('version', $this->attributes)) {
//			throw new Ajde_Component_Exception();
//		}
		if (array_key_exists('library', $this->attributes)) {
			$this->requireJsLibrary($this->attributes['library'], $this->attributes['version']);
		} elseif (array_key_exists('action', $this->attributes)) {
			$this->requireResource(
				Local::TYPE_JAVASCRIPT,
				$this->attributes['action'],
				issetor($this->attributes['format'], null),
				issetor($this->attributes['base'], null),
				issetor($this->attributes['position'], null),
				issetor($this->attributes['arguments'], '')
			);
		} elseif (array_key_exists('filename', $this->attributes)) {
			$this->requirePublicResource(
				Local::TYPE_JAVASCRIPT,
				$this->attributes['filename'],
				issetor($this->attributes['position'], null),
				issetor($this->attributes['arguments'], '')
			);
		} elseif (array_key_exists('url', $this->attributes)) {
			$this->requireRemoteResource(
				Local::TYPE_JAVASCRIPT,
				$this->attributes['url'],
				issetor($this->attributes['position'], null),
				issetor($this->attributes['arguments'], '')
			);
		}
	}
	
	public function requireJsLibrary($library, $version = false)
	{
        if ($version === false) {
            $url = JsLibrary::getCdnJsUrl($library);
        } else {
            $url = JsLibrary::getUrl($library, $version);
        }

        $resource = new Remote(AjdeResource::TYPE_JAVASCRIPT, $url);
        $this->getParser()->getDocument()->addResource($resource, Html::RESOURCE_POSITION_TOP);
	}
}