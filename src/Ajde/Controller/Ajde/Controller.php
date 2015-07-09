<?php


namespace Ajde;

use Ajde\Object\Standard;
use Config;
use Ajde\Core\Route;
use Ajde\Event\Dispatcher;
use Ajde\Core\Exception as AjdeException;
use Ajde\Core\Autoloader;
use Ajde\Http\Response;
use Ajde\Core\Exception\Routing;
use Exception as Exception;
use \Ajde;
use Ajde\View;
use Ajde\Core\Exception\Deprecated;
use Ajde\Cache;



class Controller extends Standard
{
	/**
	 * 
	 * @var Ajde_View
	 */
	protected $_view = null;
	
	/**
	 * 
	 * @var Ajde_Core_Route
	 */
	protected $_route = null;
		
	public function  __construct($action = null, $format = null)
	{
		$this->setModule(strtolower(str_replace('Controller', '', get_class($this))));
		if (!isset($action) || !isset($format)) {
			$defaultParts = Config::get('defaultRouteParts');
		}
		$this->setAction(isset($action) ? $action : $defaultParts['action']);
		$this->setFormat(isset($format) ? $format : $defaultParts['format']);
		
		$route = new Route($this->getAction());
		$route->setFormat($this->getFormat());
		$this->_route = $route;
	}
	
	public function __fallback($method, $arguments)
	{
		if (Dispatcher::has('Ajde_Controller', 'call')) {
			return Dispatcher::trigger('Ajde_Controller', 'call', array($method, $arguments));
		}
		throw new AjdeException("Call to undefined method ".get_class($this)."::$method()", 90006);		
	}	
		
	public function getModule()
	{
		return $this->get('module');
	}
	
	public function getAction()
	{
		return $this->get('action');
	}
	
	public function getFormat()
	{
		return $this->get('format');
	}
	
	public function getId()
	{
		return $this->get('id');
	}
	
	public function getRoute()
	{
		return $this->_route;
	}
	
	public function getCanonicalUrl()
	{
		return $this->_route->buildRoute();
	}

	/**
	 *
	 * @param Ajde_Core_Route $route
	 * @return Ajde_Controller
	 */
	public static function fromRoute(Route $route)
	{		
		if ($controller = $route->getController()) {
			$moduleController = ucfirst($route->getModule()) . ucfirst($controller) . 'Controller';
		} else {
			$moduleController = ucfirst($route->getModule()) . 'Controller';
		}
		if (!Autoloader::exists($moduleController)) {
			
			// Prevent resursive 404 routing
			if (isset(Config::getInstance()->responseCodeRoute[Response::RESPONSE_TYPE_NOTFOUND])) {
				$notFoundRoute = new Route(Config::getInstance()->responseCodeRoute[Response::RESPONSE_TYPE_NOTFOUND]);
				if ($route->buildRoute() == $notFoundRoute->buildRoute()) {
					Response::setResponseType(404);
					die('<h2>Ouch, something broke.</h2><p>This is serious. We tried to give you a nice error page, but even that failed.</p><button onclick="location.href=\''.Config::get('site_root').'\';">Go back to homepage</button>');
				}
			}		
					
			if (Autoloader::exists('Ajde_Exception')) {
				$exception = new Routing("Controller $moduleController for module {$route->getModule()} not found",
						90008);
			} else {
				// Normal exception here to prevent [Class 'Ajde_Exception' not found] errors...
				$exception = new Exception("Controller $moduleController for module {$route->getModule()} not found");
			}
			Ajde::routingError($exception);
		}
		$controller = new $moduleController($route->getAction(), $route->getFormat());
		$controller->_route = $route;
		foreach ($route->values() as $part => $value) {
			$controller->set($part, $value);
		}		
		return $controller;
	}

	public function invoke($action = null, $format = null)
	{
		$timerKey = Ajde::app()->addTimer((string) $this->_route);
		$action = issetor($action, $this->getAction());
		$format = issetor($format, $this->getFormat());
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        $tryTheseFunctions = array();

        $formatFunction = $action . ucfirst($format);
        $defaultFunction = $action . "Default";
        $emptyFunction = $action;

        $tryTheseFunctions[] = $formatFunction . ucfirst($method);
        $tryTheseFunctions[] = $defaultFunction . ucfirst($method);
        $tryTheseFunctions[] = $emptyFunction . ucfirst($method);
        $tryTheseFunctions[] = $formatFunction;
        $tryTheseFunctions[] = $defaultFunction;
        $tryTheseFunctions[] = $emptyFunction;

        $invokeFunction = '';

        foreach($tryTheseFunctions as $tryFunction) {
            if (method_exists($this, $tryFunction)) {
                $invokeFunction = $tryFunction;
                break;
            }
        }
//        dump(get_class($this) . '::' .  $invokeFunction);

		if (!$invokeFunction) {
			$exception = new Routing(sprintf("Action %s for module %s not found",
						$this->getAction(),
						$this->getModule()
					), 90011);
			Ajde::routingError($exception);
		}

		$return = true;
		if (method_exists($this, 'beforeInvoke')) {
			$return = $this->beforeInvoke();
			if ($return !== true && $return !== false) {
				// TODO:
				throw new AjdeException(sprintf("beforeInvoke() must return either TRUE or FALSE"));
			}
		}		
		if ($return === true) {
			$return = $this->$invokeFunction();
			if (method_exists($this, 'afterInvoke')) {
				$this->afterInvoke();
			}	
		}			
		Ajde::app()->endTimer($timerKey);
		return $return;

	}

	/**
	 * 
	 * @return Ajde_View
	 */
	public function getView()
	{
		if (!isset($this->_view)) {
			$this->_view = View::fromController($this);
		}
		return $this->_view;
	}
	
	/**
	 * 
	 * @param Ajde_View $view
	 */
	public function setView(View $view)
	{
		$this->_view = $view;
	}
	
	/**
	 * Shorthand for $controller->getView()->getContents();
	 */
	public function render()
	{
		$return = true;
		if (method_exists($this, 'beforeRender')) {
			$return = $this->beforeRender();
			if ($return !== true && $return !== false) {
				// TODO:
				throw new AjdeException(sprintf("beforeRender() must return either TRUE or FALSE"));
			}
		}
		if ($return === true) {
			return $this->getView()->getContents();
		}		
	}
	
	public function loadTemplate()
	{
		throw new Deprecated();
		$view = View::fromController($this);
		return $view->getContents();
	}

	public function redirect($route = Response::REDIRECT_SELF)
	{
		Ajde::app()->getResponse()->setRedirect($route);
	}
	
	public function rewrite($route)
	{
		Ajde::app()->getResponse()->dieOnRoute($route);
	}
	
	public function updateCache()
	{
		// TODO:
		throw new Deprecated();
	}
	
	/**
	 *
	 * @param Ajde_Model|Ajde_Collection $object 
	 */
	public function touchCache($object = null)
	{
		Cache::getInstance()->updateHash(isset($object) ? $object->hash() : time());
	}
}