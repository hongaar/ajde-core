<?php


namespace Ajde\Crud;

use Ajde\Object\Standard;



class Options extends Standard
{
	protected $_key;
	
	/**
	 *
	 * @var Ajde_Crud_Options 
	 */
	protected $_parent = null;
		
	/**
	 *
	 * @var array 
	 */
	public $_stack = array();
	
	public function __construct() {
		$this->_active = $this;
	}
	
	// Protected functions
	
	protected function _select($name, $key = null)
	{
		$key = isset($key) ? $key : $name;
		// Get new active object
		$className = get_class($this) . '_' . ucfirst($name);
		/* @var $new Ajde_Crud_Options */
		$new = new $className();		
		$new->_parent = $this;
		$new->_key = $key;
		if (isset($this->_stack[$key])) {
			$new->_stack = $this->_stack[$key];
		}
		return $new;
	}
		
	protected function _set($key, $value)
	{
		parent::_set($key, $value);
		return $this;
	}
	
	// Public functions
	
	/**
	 *
	 * @return Ajde_Crud_Options 
	 */
	public function up($obj = false)
	{
		if (!$obj) { $obj = $this; }
		if (!isset($obj->_parent)) {
			return false;
		}
		$obj->_parent->_stack[$obj->_key] = array_merge($obj->_stack, $obj->values());
		return $obj->_parent;
	}
	
	/**
	 *
	 * @return Ajde_Crud_Options
	 */
	public function finished()
	{
		$test = $this->up();
		while ($test) {
			$test = $test->up();
		}
		return $this;
	}
	
	/**
	 *
	 * @return Ajde_Crud_Options
	 */	
	public function display()
	{
		var_dump($this->_stack);
		return $this;
	}
	
	/**
	 *
	 * @return array
	 */
	public function getArray()
	{
		return $this->_stack;
	}
	
	// =========================================================================
	// Select functions
	// =========================================================================
	
	/**
	 *
	 * @return Ajde_Crud_Options_Fields
	 */
	public function selectFields()	{ return $this->_select('fields'); }
	
	/**
	 *
	 * @return Ajde_Crud_Options_List 
	 */
	public function selectList()	{ return $this->_select('list'); }
	
	/**
	 *
	 * @return Ajde_Crud_Options_Edit
	 */
	public function selectEdit()	{ return $this->_select('edit'); }
	
	// =========================================================================
	// Set functions
	// =========================================================================
	
}