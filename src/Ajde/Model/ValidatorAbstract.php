<?php


namespace Ajde\Model;

use Ajde\Object\Standard;
use Ajde\Model;



abstract class ValidatorAbstract extends Standard
{	
	protected $_value = null;
	protected $_model = null;
	
	protected $_defaultOptions = array(
		'length' => null,
		'default' => null,
		'isRequired' => false,
		'isPK' => false,
		'isAutoIncrement' => false,
		'isAutoUpdate' => false,
		'isUnique' => false
	);
	
	public function __construct(Model $model = null)
	{
		$this->_model = $model;
		$this->_data = $this->_defaultOptions;
	}
		
	/**
	 * Getters and setters
	 */
	
	/**
	 *
	 * @return Ajde_Model
	 */
	public function getModel()			{ return $this->_model; }
	public function setModel($model)	{ $this->_model = $model; }
	
	public function getName()			{ return parent::getName(); }
	public function getDbType()			{ return parent::getDbType(); }
	public function getLabel()			{ return parent::getLabel(); }
	public function getLength()			{ return parent::getLength(); }
	public function getIsRequired()		{ return parent::getIsRequired(); }
	public function getDefault()		{ return parent::getDefault(); }
	public function getIsAutoIncrement(){ return parent::getIsAutoIncrement(); }
	public function getIsUnique()		{ return parent::getIsUnique(); }
	
	public function getValue()
	{
		return $this->_value;
	}
	
	public function validate($fieldOptions, $value)
	{
		$this->_value = $value;
		
		/* options */
		foreach($fieldOptions as $key => $value) {
			$this->set($key, $value);
		}
		return $this->_validate();
	}
	
	/**
	 * @return array('valid' => true|false, ['error' => (string)]);
	 */
	abstract protected function _validate();
}