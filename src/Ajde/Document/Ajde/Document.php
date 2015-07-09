<?php


namespace Ajde;

use Ajde\Object\Standard;
use Ajde\Core\Route;
use Ajde\Core\Autoloader;
use Ajde\Core\Exception\Routing;
use \Ajde;
use Ajde\Layout;
use Config;
use Ajde\Resource;
use Ajde\Core\Exception\Deprecated;
use Ajde\Core\Exception;
use Ajde\Event\Dispatcher;



abstract class Document extends Standard
{
	const CACHE_CONTROL_PUBLIC = 'public';
	const CACHE_CONTROL_PRIVATE = 'private';
	const CACHE_CONTROL_NOCACHE = 'no-cache';
	
	protected $_cacheControl = self::CACHE_CONTROL_PUBLIC;
	protected $_contentType = 'text/html';
	protected $_maxAge = 604800; // 1 week
	
	public function  __construct()
	{
		$this->setFormat(strtolower(str_replace("Ajde_Document_Format_", '', get_class($this))));
	}
	
	/**
	 *
	 * @param Ajde_Core_Route $route
	 * @return Ajde_Document
	 */
	public static function fromRoute(Route $route)
	{
		$format = $route->getFormat();
		$documentClass = "Ajde_Document_Format_" . ucfirst($format);
		if (!Autoloader::exists($documentClass)) {
			$exception = new Routing("Document format $format not found",
					90009);
			Ajde::routingError($exception);
		}
		return new $documentClass();
	}
	
	/**
	 * @return Ajde_Layout
	 */
	public function setLayout(Layout $layout)
	{
		if (! $layout instanceof Layout) {
			$layout = new Layout($layout);
		}
		$layout->setDocument($this);
		return $this->set("layout", $layout);
	}	

	/**
	 * @return Ajde_Layout
	 */
	public function getLayout()
	{
		if (!$this->hasLayout())
		{
			// Load default layout into document
			$this->setLayout(new Layout(Config::get("layout")));
		}		
		return $this->get("layout");
	}

	/**
	 *
	 * @param string $contents
	 */
	public function setBody($contents)
	{
		$this->set('body', $contents);
	}

	/**
	 *
	 * @return string
	 */
	public function getBody()
	{
		if ($this->has('body')) {
			return $this->get('body');
		} else {
			return '';
		}
	}
	
	public function setTitle($title)
	{
		$this->set('title', $title);
	}
	
	public function getTitle()
	{
		return $this->has('title') ? $this->get('title') : __('Untitled page');
	}
	
	public function getFullTitle()
	{
		$projectTitle = Config::get('sitename');
		if ($this->has('title')) {
			return sprintf(Config::get('titleFormat'),
				$projectTitle,
				$this->get('title')
			);
		} else {
			return $projectTitle;
		}
	}
	
	public function setDescription($description)
	{
		$this->set('description', $description);
	}
	
	public function getDescription()
	{
		if ($this->has('description')) {
			return $this->get('description');
		} else {
			return Config::get('description');
		}
	}

    public function setAuthor($author)
    {
        $this->set('author', $author);
    }

    public function getAuthor()
    {
        if ($this->has('author')) {
            return $this->get('author');
        } else {
            return Config::get('author');
        }
    }

    public function render()
	{
		return $this->getLayout()->getContents();
	}
	
	public function getCacheControl()
	{
		return $this->_cacheControl;
	}
	
	public function setCacheControl($cacheControl)
	{
		$this->_cacheControl = $cacheControl;
	}
	
	public function getContentType()
	{
		return $this->_contentType;
	}
	
	public function setContentType($mimeType)
	{
		$this->_contentType = $mimeType;
	}
	
	public function getMaxAge()
	{
		return (int) $this->_maxAge;
	}
	
	public function setMaxAge($days)
	{
		$this->_maxAge = (60*60*24 * (int) $days);
	}

	/**
	 *
	 * @param Ajde_Resource $resource
	 */
	public function addResource(Resource $resource) {}

	public function getResourceTypes() {}
	
	// Render helpers
	
	/**
	 *
	 * @deprecated
	 * @throws Ajde_Core_Exception_Deprecated 
	 */
	protected function setContentTypeHeader($contentType = null)
	{
		throw new Deprecated();
	}
	
	/**
	 *
	 * @deprecated
	 * @throws Ajde_Core_Exception_Deprecated 
	 */
	protected function setCacheControlHeader($cacheControl = null)
	{
		throw new Deprecated();
	}
	
	public static function registerDocumentProcessor($format, $registerOn = 'layout')
	{
		$documentProcessors = Config::get('documentProcessors');
		if (is_array($documentProcessors) && isset($documentProcessors[$format])) {
			foreach($documentProcessors[$format] as $processor) {
				$processorClass = 'Ajde_Document_Processor_' . ucfirst($format) . '_' . $processor;
				if (!Autoloader::exists($processorClass)) {
					// TODO:
					throw new Exception('Processor ' . $processorClass . ' not found', 90022);
				}
				if ($registerOn == 'layout') {
					Dispatcher::register('Ajde_Layout', 'beforeGetContents', $processorClass . '::preProcess');
					Dispatcher::register('Ajde_Layout', 'afterGetContents', $processorClass . '::postProcess');
				} elseif($registerOn == 'compressor') {
					Dispatcher::register('Ajde_Resource_Local_Compressor', 'beforeCompress', $processorClass . '::preCompress');
					Dispatcher::register('Ajde_Resource_Local_Compressor', 'afterCompress', $processorClass . '::postCompress');
				} else {
					// TODO:
					throw new Exception('Document processor must be registered on either \'layout\' or \'compressor\'');
				}
			}
		}
	}
}