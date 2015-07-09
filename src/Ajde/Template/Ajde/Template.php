<?php


namespace Ajde;

use Ajde\Object\Standard;
use Ajde\Core\Exception\Routing;
use \Ajde;
use Ajde\Core\Exception;
use Ajde\FileSystem\Find;
use Ajde\Template\Parser;
use Ajde\Event\Dispatcher;
use Ajde\Cache;
use Ajde\Document\Format\Html;



class Template extends Standard
{
	protected $_contents = null;
	protected $_table = array();

	public function  __construct($base, $action, $format = 'html')
	{
		$this->set('base', $base);
		$this->set('action', $action);
		$this->set('format', $format);
		$this->setFileinfo();
	}

	protected function setFileinfo()
	{
		if (($fileInfo = $this->getFileInfo()) === false) {
			$exception = new Routing(sprintf("Template file in %s,
					for action %s with format %s not found",
					$this->getBase(), $this->getAction(), $this->getFormat()), 90010);
			Ajde::routingError($exception);
		}
		$className = 'Ajde_Template_Parser_' . $fileInfo['parser'];
		$parser = new $className($this);

		$this->setFilename($fileInfo['filename']);
		$this->setParser($parser);
	}

	public function setBase($base)
	{
		$this->set('base', $base);
		$this->setFileinfo();
	}

	public function setAction($action)
	{
		$this->set('action', $action);
		$this->setFileinfo();
	}

	public function setFormat($format)
	{
		$this->set('format', $format);
		$this->setFileinfo();
	}

	public function __fallback($method, $arguments)
	{
		$helper = $this->getParser()->getHelper();
		if (method_exists($helper, $method)) {
			return call_user_func_array(array($helper, $method), $arguments);
		} else {
			throw new Exception("Call to undefined method ".get_class($this)."::$method()", 90006);
		}
	}

	protected function getFileInfo()
	{
		return $this->_getFileInfo($this->getBase(), $this->getAction(), $this->getFormat());
	}

	private static function _getFileInfo($base, $action, $format = 'html')
	{
		// go see what templates are available
		$dirPrefixPatterns = array(
				APP_DIR, CORE_DIR
		);
		$fileNamePatterns = array(
				$action . '.' . $format, $action
		);
		$fileTypes = array(
				'phtml' => 'Phtml', 'xhtml' => 'Xhtml'
		);
		foreach($dirPrefixPatterns as $dirPrefixPattern) {
			$prefixedBase = $dirPrefixPattern . $base;
			foreach($fileNamePatterns as $fileNamePattern) {
				foreach($fileTypes as $fileType => $parserType) {
					$filePattern = $fileNamePattern . '.' . $fileType;
					if (!substr_count($prefixedBase, DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR)) {
						$layoutDir = 'layout.' . Ajde::app()->getDocument()->getLayout()->getName() . DIRECTORY_SEPARATOR;
						if ($fileMatch = Find::findFile($prefixedBase . TEMPLATE_DIR . $layoutDir, $filePattern)) {
							return array('filename' => $fileMatch, 'parser' => $parserType);
						}
					}
					if ($fileMatch = Find::findFile($prefixedBase . TEMPLATE_DIR, $filePattern)) {
						return array('filename' => $fileMatch, 'parser' => $parserType);
					}
				}
			}
		}
		return false;
	}

	public function setParser(Parser $parser)
	{
		$this->set('parser', $parser);
	}

	/**
	 *
	 * @return Ajde_Template_Parser
	 */
	public function getParser()
	{
		return $this->get('parser');
	}

	public static function exist($base, $action, $format = 'html')
	{
		return self::_getFileInfo($base, $action, $format);
	}

	public function setFilename($filename)
	{
		$this->set('filename', $filename);
	}

	public function getFilename()
	{
		return $this->get("filename");
	}

	public function getBase()
	{
		return $this->get('base');
	}

	public function getAction()
	{
		return $this->get('action');
	}

	public function getFormat()
	{
		return $this->get('format');
	}

	public function assign($key, $value)
	{
		$this->_table[$key] = $value;
	}

	public function assignArray($array)
	{
		foreach($array as $key => $value) {
			$this->assign($key, $value);
		}
	}

	public function hasAssigned($key)
	{
		return array_key_exists($key, $this->_table);
	}

	public function getAssigned($key)
	{
		return $this->_table[$key];
	}

    public function getAllAssigned()
    {
        return $this->_table;
    }

	public function getContents()
	{
		if (!isset($this->_contents))
		{
			Dispatcher::trigger($this, 'beforeGetContents');
			Cache::getInstance()->addFile($this->getFilename());
			$contents = $this->getParser()->parse($this);
			$this->setContents($contents);
			Dispatcher::trigger($this, 'afterGetContents');
		}
		return $this->_contents;
	}

	/**
	 * Alias for $this->getContents()
	 * @see self::getContents()
	 */
	public function render()
	{
		return $this->getContents();
	}

	public function setContents($contents)
	{
		$this->_contents = $contents;
	}

	public function getDefaultResourcePosition()
	{
		return Html::RESOURCE_POSITION_DEFAULT;
	}
}