<?php


namespace Ajde\Crud;

use Ajde\Object\Standard;
use Ajde\Crud;
use Ajde\Template;
use \Ajde;
use Ajde\Exception;



abstract class Field extends Standard
{
	/**
	 *
	 * @var Ajde_Crud
	 */
	protected $_crud;

	/**
	 *
	 * @var string
	 */
	protected $_type;
	
	protected $_useSpan = 12;
	protected $_attributes = array();
	
	public function __construct(Crud $crud, $fieldOptions) {
		$explode = explode('_', get_class($this));
		end($explode);
		$this->_type = strtolower(current($explode));
		$this->_crud = $crud;
		
		/* defaults */
		$this->_data = array(
			'name' => isset($fieldOptions['name']) ? $fieldOptions['name'] : false,
			'type' => 'text',
			'length' => 255,
			'default' => '',
			'label' => isset($fieldOptions['name']) ? ucfirst($fieldOptions['name']) : false,
			'isRequired' => false,
			'isPK' => false,				
			'isAutoIncrement' => false,
			'isAutoUpdate' => false
		);

		/* options */
		foreach($fieldOptions as $key => $value) {
			$this->set($key, $value);
		}

		$this->prepare();
	}

	protected function prepare()
	{
	}

	/**
	 * Getters and setters
	 */

	public function getName()			{ return parent::getName(); }
	public function getDbType()			{ return parent::getDbType(); }
	public function getLabel()			{ return parent::getLabel(); }
	public function getLength()			{ return parent::getLength(); }
	public function getIsRequired()		{ return parent::getIsRequired(); }
	public function getDefault()		{ return parent::getDefault(); }
	public function getIsAutoIncrement(){ return parent::getIsAutoIncrement(); }
	public function getIsAutoUpdate()	{ return parent::getIsAutoUpdate(); }
	public function getIsUnique()		{ return parent::getIsUnique(); }

	public function getValue()			{
		if (parent::hasValue()) {
			return parent::getValue();
		} else {
			return false;
		}
	}

	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Template functions
	 */

	public function getHtml()
	{
		$template = $this->_getFieldTemplate();
		$template->assign('field', $this);
		return $template->render();
	}

	public function getInput($id = null)
	{
		$template = $this->_getInputTemplate();
		$template->assign('field', $this);
		$template->assign('id', $id);
		return $template->render();
	}

	protected function _getFieldTemplate()
	{
		return $this->_getTemplate('field');
	}

	protected function _getInputTemplate()
	{
		return $this->_getTemplate('field/' . $this->_type);
	}

	protected function _getTemplate($action)
	{
		$template = null;
		if (Template::exist(MODULE_DIR . '_core/', 'crud/' . $action) !== false) {
			$template = new Template(MODULE_DIR . '_core/', 'crud/' . $action);
			Ajde::app()->getDocument()->autoAddResources($template);
		}
		if ($this->_hasCustomTemplate($action)) {
			$base = $this->_getCustomTemplateBase();
			$action = $this->_getCustomTemplateAction($action);
			$template = new Template($base, $action);
		}
		if ($template instanceof Template) {
			return $template;
		} else {
			// TODO:
			throw new Exception('No crud template found for field ' . $action);
		}
	}

	protected function _hasCustomTemplate($action)
	{
		$base = $this->_getCustomTemplateBase();
		$action = $this->_getCustomTemplateAction($action);
		return Template::exist($base, $action) !== false;
	}

	protected function _getCustomTemplateBase()
	{
		return MODULE_DIR . $this->_crud->getCustomTemplateModule() . '/';
	}

	protected function _getCustomTemplateAction($action)
	{
		return 'crud/' . (string) $this->_crud->getModel()->getTable() . '/' . $action;
	}

	/**
	 * HTML functions
	 */

	public function getHtmlRequired()
	{
		//return '<span class="required">*</span>';
		return '';
	}

	public function getHtmlPK()
	{
		return " <img src='" . MEDIA_DIR . "icons/16/key_login.png' style='vertical-align: middle;' title='Primary key' />";
	}
	
	public function getHtmlAttributesAsArray()
	{
		if (empty($this->_attributes)) {
			$attributes = array();
			if (method_exists($this, '_getHtmlAttributes')) {
				$attributes = $this->_getHtmlAttributes();
			}
			$attributes['name'] = $this->getName();
			if (!key_exists('id', $attributes)) {
				$attributes['id'] = 'in_' . $this->getName();
			}
            if ($this->hasNotEmpty('class')) {
                if (key_exists('class', $attributes)) {
                    $attributes['class'] .= ' ' . $this->getClass();
                } else {
                    $attributes['class'] = $this->getClass();
                }
            }
			if ($this->_useSpan !== false) {
				if (key_exists('class', $attributes)) {
					$attributes['class'] .= ' span' . $this->_useSpan;
				} else {
					$attributes['class'] = 'span' . $this->_useSpan;
				}
			}
			$this->_attributes = $attributes;
		}
		return $this->_attributes;
	}
	
	public function getHtmlAttribute($name)
	{
		$attributes = $this->getHtmlAttributesAsArray();
		if (isset($attributes[$name])) {
			return $attributes[$name];
		}
		return false;
	}

	public function getHtmlAttributes()
	{
		$attributes = $this->getHtmlAttributesAsArray();
        
        $text = '';
        foreach ($attributes as $k => $v) {
			if (strlen($v)) {
				$text .= $k . '="' . $v . '" ';
			}
        }
		return $text;
	}

	/**
	 *
	 * @return Ajde_Crud
	 */
	public function getCrud()
	{
		return $this->_crud;
	}

}
