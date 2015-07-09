<?php


namespace Ajde;

use Ajde\Object\Singleton;
use Ajde\Http\Response;
use Ajde\Event;
use Ajde\Core\Bootstrap;
use Ajde\Http\Request;
use Ajde\Document;
use Ajde\Controller;
use Ajde\Cache;
use Exception;
use Config;
use Ajde\Core\Autoloader;
use Ajde\Exception\Log;



class Application extends Singleton
{
	protected $_timers = array();
	protected $_timerLevel = 0;
	
	/**
	 *
	 * @staticvar Ajde_Application $instance
	 * @return Ajde_Application
	 */
	public static function getInstance()
	{
		static $instance;
		return $instance === null ? $instance = new self : $instance;
	}

	/**
	 *
	 * @return Ajde_Application
	 */
	public static function app()
	{
		return self::getInstance();
	}

	/**
	 *
	 * @return Ajde_Application
	 */
	public static function create()
	{
		return self::getInstance();
	}
	
	public function addTimer($description)
	{		
		$this->_timers[] = array(
			'description' => $description,
			'level' => $this->_timerLevel,
			'start' => microtime(true),
			'end' => null,
			'total' => null);
		$this->_timerLevel++;		
		return $this->getLastTimerKey();
	}
	
	public function getLastTimerKey()
	{
		end($this->_timers);		
		return key($this->_timers);
	}
	
	public function endTimer($key)
	{
		$this->_timerLevel--;
		$this->_timers[$key]['end'] = $end = microtime(true);
		$this->_timers[$key]['total'] = round(($end - $this->_timers[$key]['start']) * 1000, 0);
		return $this->_timers[$key]['total'];
	}
	
	public function getTimers()
	{
		return $this->_timers;
	}

	public function run()
	{
		// For debugger
		$this->addTimer('<i>Application</i>');
		
		// Create fresh response
		$timer = $this->addTimer('Create response');
			$response = new Response();
			$this->setResponse($response);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterResponseCreated');

		// Bootstrap init
		$timer = $this->addTimer('Run bootstrap queue');
			$bootstrap = new Bootstrap();
			$bootstrap->run();
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterBootstrap');

		// Get request
		$timer = $this->addTimer('Read in global request');
			$request = Request::fromGlobal();
			$this->setRequest($request);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterRequestCreated');

		// Get route
		$timer = $this->addTimer('Initialize route');
			$route = $request->initRoute();
			$this->setRoute($route);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterRouteInitialized');

		// Load document
		$timer = $this->addTimer('Create document');
			$document = Document::fromRoute($route);
			$this->setDocument($document);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterDocumentCreated');
		
		// Load controller
		$timer = $this->addTimer('Load controller');
			$controller = Controller::fromRoute($route);
			$this->setController($controller);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterControllerCreated');

		// Invoke controller action
		$timer = $this->addTimer('Invoke controller');
			$actionResult = $controller->invoke();
			$document->setBody($actionResult);
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterControllerInvoked');

		// Get document contents
		$timer = $this->addTimer('Render document');
			$contents = $document->render();
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterDocumentRendered');
		
		// Let the cache handle the contents and have it saved to the response
		$timer = $this->addTimer('Save to response');
			$cache = Cache::getInstance();
			$cache->setContents($contents);
			$cache->saveResponse();
		$this->endTimer($timer);
		
		Event::trigger($this, 'onAfterResponseCreated');

		// Output the buffer
		$response->send();
		
		Event::trigger($this, 'onAfterResponseSent');
	}

	public static function routingError(Exception $exception)
	{
		if (Config::get("debug") === true) {
			throw $exception;
		} else {
			if (Autoloader::exists('Ajde_Exception_Log')) {
				Log::logException($exception);
			}
			Response::redirectNotFound();
		}
	}

	/**
	 *
	 * @return Ajde_Http_Request
	 */
	public function getRequest() {
		return $this->get("request");
	}

	/**
	 *
	 * @return Ajde_Http_Response
	 */
	public function getResponse() {
		return $this->get("response");
	}

	/**
	 *
	 * @return Ajde_Core_Route
	 */
	public function getRoute() {
		return $this->get("route");
	}

	/**
	 *
	 * @return Ajde_Document
	 */
	public function getDocument() {
		return $this->get("document");
	}

	/**
	 *
	 * @return Ajde_Controller
	 */
	public function getController() {
		return $this->get("controller");
	}

	public static function includeFile($filename)
	{
		Cache::getInstance()->addFile($filename);
		include $filename;
	}

	public static function includeFileOnce($filename)
	{
		Cache::getInstance()->addFile($filename);
		include_once $filename;
	}

}