<?php


namespace Ajde;

use Ajde\Template;
use Ajde\Controller;
use Ajde\Core\Route;



class View extends Template
{	
	/**
	 * 
	 * @param Ajde_Controller $controller
	 * @return Ajde_View
	 */
	public static function fromController(Controller $controller) {
		$base = MODULE_DIR. $controller->getModule() . '/'; 
		$action = $controller->getRoute()->getController() ?
			$controller->getRoute()->getController() . '/' . $controller->getAction() :
			$controller->getAction();			
		$format = $controller->hasFormat() ? $controller->getFormat() : 'html';			
		return new self($base, $action, $format);
	}

	/**
	 *
	 * @param Ajde_Core_Route $route
	 * @return Ajde_View
	 */
	public static function fromRoute($route)
	{
		if (!$route instanceof Route) {
			$route = new Route($route);
		}
		$base = MODULE_DIR. $route->getModule() . '/';
		$action = $route->getController() ?
			$route->getController() . '/' . $route->getAction() :
			$route->getAction();
		$format = $route->getFormat();
		return new self($base, $action, $format);
	}
}