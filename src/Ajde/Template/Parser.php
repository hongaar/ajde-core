<?php 

namespace Ajde\Template;

use Ajde\Object\Standard;
use Ajde\Template;
use Ajde\Core\Exception;
use Ajde\Template\Parser\Phtml\Helper;




class Parser extends Standard
{
	/**
	 * 
	 * @var Ajde_Template
	 */
	protected $_template = null;
	
	/**
	 * 
	 * @var Ajde_Template_Parser_Phtml_Helper
	 */
	protected $_helper = null;
	
	/**
	 * 
	 * @param Ajde_Template $template
	 */
	public function __construct(Template $template)
	{
		$this->_template = $template;
	}
	
	public function __isset($name)
	{
		$template = $this->getTemplate();
		return $template->hasAssigned($name);
	}
	
	public function __get($name)
	{
		$template = $this->getTemplate();
		if ($template->hasAssigned($name)) {
			return $template->getAssigned($name);
		} else {
			throw new Exception("No variable with name '" . $name . "' assigned to template.", 90019);
		}
	}
	
	public function __fallback($method, $arguments)
	{
		$helper = $this->getHelper();
		if (method_exists($helper, $method)) {
			return call_user_func_array(array($helper, $method), $arguments);
		} else {
			throw new Exception("Call to undefined method ".get_class($this)."::$method()", 90006);
		}
    }
	
	/**
	 * 
	 * @return Ajde_Template_Parser_Phtml_Helper
	 */
	public function getHelper()
	{
		if (!isset($this->_helper)) {
			$this->_helper = new Helper($this); 
		}
		return $this->_helper;
	}

	/**
	 * 
	 * @return Ajde_Template
	 */
	public function getTemplate()
	{
		return $this->_template;
	}
	
	public function parse()
	{
		return $this->_getContents();
	}
	
	protected function _getContents()
	{
		ob_start();
		include $this->getTemplate()->getFilename();
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
}