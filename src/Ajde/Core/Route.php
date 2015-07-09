<?php


namespace Ajde\Core;

use Ajde\Object\Standard;
use Ajde\Lang;
use Ajde\Event\Dispatcher;
use Config;
use Ajde\Core\Exception\Routing;
use \Ajde;
use Ajde\Core\Exception\Deprecated;
use Ajde\Core\Exception;



class Route extends Standard
{
    protected $_originalRoute = null;
	protected $_route = null;

	public function __construct($route)
	{
        $this->_originalRoute = $route;
		// See if first part is language code (i.e. first part is exactly
		// two characters in length)
		if (strlen($route) === 2 || substr($route, 2, 1) === '/') {
			$shortLang = substr($route, 0, 2);
			$langInstance = Lang::getInstance();
			if ($lang = $langInstance->getAvailableLang($shortLang)) {
				$this->set("lang", $lang);
				$route = substr($route, 3);
                // set global lang
                $langInstance->setGlobalLang($lang);
			}
		}
        Dispatcher::trigger($this, 'onAfterLangSet');
		if (!$route) {
			$route = Config::get('homepageRoute');
		}
		// Check for route aliases
		$aliases = Config::get("aliases");
		if (array_key_exists($route, $aliases)) {
			$this->_route = $aliases[$route];
		} else {
			$this->_route = $route;
		}
        Dispatcher::trigger($this, 'onAfterRouteSet');
		// Get route parts
		$routeParts = $this->_extractRouteParts();
		if (empty($routeParts)) {
			$exception = new Routing(sprintf("Invalid route: %s",
					$route), 90021);
			Ajde::routingError($exception);
		}
		$defaultParts = Config::get('defaultRouteParts');
		$parts = array_merge($defaultParts, $routeParts);
		foreach($parts as $part => $value) {
			$this->set($part, $value);
		}
	}
	
	public function __toString()
	{
		return $this->_route = $this->buildRoute();
	}
	
	public function buildRoute($includeLang = true)
	{
		$route = '';
		if ($includeLang && $this->hasLang()) {
			$route .= substr($this->getLang(), 0, 2) . '/';
		}
		$route .= $this->getModule() . '/';
		if ($this->getController()) {
			$route .= $this->getController() . ':';
		}
		$route .= $this->getAction() . '/' . $this->getFormat();
		if ($this->hasNotEmpty('id')) {
			$route .= '/' . $this->getId();
		}
		return $route;
	}

    public function getRoute()
    {
        return $this->_route;
    }

    public function getOriginalRoute()
    {
        return $this->_originalRoute;
    }

    public function setRoute($route)
    {
        $this->_route = $route;
    }
	
	public function getModule($default = null) {
		if (isset($default)) {
			throw new Deprecated();
		}
		return $this->get("module", $default);
	}

	public function getController($default = null) {
		if (isset($default)) {
			throw new Deprecated();
		}
		return $this->get("controller", $default);
	}
	
	public function getAction($default = null) {
		if (isset($default)) {
			throw new Deprecated();
		}
		return $this->get("action", $default);
	}

	public function getFormat($default = null) {
		if (isset($default)) {
			throw new Deprecated();
		}
		return $this->get("format", $default);
	}
	
	public function getLang($default = null) {
		if (isset($default)) {
			throw new Deprecated();
		}
		return $this->get("lang", $default);
	}
	
	protected function _extractRouteParts()
	{
		$matches = array();
		$defaultRules = array(
			// module/controller:view
			array('%^([^/\.]+)/([^/\.]+):([^/\.]+)/?$%' => array('module', 'controller', 'action')),
			// module/controller:view/html
			array('%^([^/\.]+)/([^/?/\.]+):([^/?/\.]+)/([^/\.]+)/?$%' => array('module', 'controller', 'action', 'format')),
			// module/controller:view/html/5
			array('%^([^/\.]+)/([^/?/\.]+):([^/?/\.]+)/([^/\.]+)/([^/\.]+)/?$%' => array('module', 'controller', 'action', 'format', 'id')),
			// module/controller:view.html
			array('%^([^/\.]+)/([^/\.]+):([^/\.]+)\.([^/\.]+)$%' => array('module', 'controller', 'action', 'format')),
			// module/controller:view/5.html
			array('%^([^/\.]+)/([^/\.]+):([^/\.]+)/([^/\.]+)\.([^/\.]+)$%' => array('module', 'controller', 'action', 'id', 'format')),
		
			// module
			array('%^([^/\.]+)/?$%' => array('module')),
			// module/5
			array('%^([^/\.]+)/([0-9]+)/?$%' => array('module', 'id')),
			// module/view
			array('%^([^/\.]+)/([^/\.]+)/?$%' => array('module', 'action')),			
			// module/view/html
			array('%^([^/\.]+)/([^/\.]+)/([^/\.]+)/?$%' => array('module', 'action', 'format')),
			// module/view/html/5
			array('%^([^/\.]+)/([^/\.]+)/([^/\.]+)/([^/\.]+)/?$%' => array('module', 'action', 'format', 'id')),
			
			// module.html
			array('%^([^/\.]+)\.([^/\.]+)$%' => array('module', 'format')),
			// module/5.html
			array('%^([^/\.]+)/([0-9]+)\.([^/\.]+)$%' => array('module', 'id', 'format')),
			// module/view.html
			array('%^([^/\.]+)/([^/\.]+)\.([^/\.]+)$%' => array('module', 'action', 'format')),
			// module/view/5.html
			array('%^([^/\.]+)/([^/\.]+)/([^/\.]+)\.([^/\.]+)$%' => array('module', 'action', 'id', 'format')),
		);
		
		$configRules = Config::get('routes');
		$rules = array_merge($configRules, $defaultRules);

		foreach($rules as $rule)
		{
			$pattern = key($rule);
			$parts = current($rule);
			if (preg_match($pattern, $this->_route, $matches))
			{
				// removes first element of matches
				array_shift($matches);
				if (count($parts) != count($matches))
				{
					throw new Exception("Number of routeparts does not match regular expression", 90020);	
				} 
				return array_combine($parts, $matches);
			}	
		}
		
	}
}