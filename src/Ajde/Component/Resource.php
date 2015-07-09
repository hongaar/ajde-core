<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Document\Format\Html;
use Ajde\Resource\Local;
use Ajde\Resource\Public;
use Ajde\Resource\Remote;




class Resource extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	public function process()
	{
		return false;
	}
	
	public function requireResource($type, $action, $format = 'html', $base = null, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		if (!isset($base)) {
			$base = $this->getParser()->getTemplate()->getBase();			
		}
		$resource = new Local($type, $base, $action, $format, $arguments);
		$this->getParser()->getDocument()->addResource($resource, $position);
	}
	
	public function requirePublicResource($type, $filename, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		$resource = new Public($type, $filename, $arguments);
		$this->getParser()->getDocument()->addResource($resource, $position);
	}
	
	public function requireRemoteResource($type, $url, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		$resource = new Remote($type, $url, $arguments);
		$this->getParser()->getDocument()->addResource($resource, $position);
	}
}