<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Component\Exception as AjdeComponentException;
use Ajde\Core\Route;
use Ajde\Controller;
use Exception as Exception;




class Include extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	public function process()
	{
		if (!array_key_exists('route', $this->attributes)) {
			// TODO:
			throw new AjdeComponentException();
		}
		$route = $this->attributes['route'];
		if (!$route instanceof Route) {
			$route = new Route($route);
		}
		$controller = Controller::fromRoute($route);
		if (array_key_exists('vars', $this->attributes) && is_array($this->attributes['vars']) && !empty($this->attributes['vars'])) {
			try {
				$view = $controller->getView();
				foreach($this->attributes['vars'] as $key => $var) {
					$view->assign($key, $var);
				}
			} catch(Exception $e) {}
		}
		return $controller->invoke();
	}
}