<?php


namespace Ajde\Crud\Cms\Meta;

use Ajde\Object\Standard;
use Ajde\Crud\Options\Fields\Field;



abstract class Fieldlist extends Standard
{	
	private $_fields = array();
	
	protected function addField(Field $field)
	{
		$this->_fields[$field->getName()] = $field;
	}
	
	public function getFields()
	{
		return $this->_fields;
	}
	
	public function hasField($name)
	{
		return isset($this->_fields[$name]);
	}
	
	public function hasFields()
	{
		return !empty($this->_fields);
	}
	
	public function getField($name)
	{
		return $this->_fields[$name];
	}
	
	public function setField($name, $field)
	{
		$this->_fields[$name] = $field;
	}
	
	public function setFields($fields)
	{
		$this->_fields = $fields;
	}
	
	public function getFieldNames()
	{
		return array_keys($this->getFields());
	}
}