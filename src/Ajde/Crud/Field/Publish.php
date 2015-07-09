<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Publish extends Field
{
	const MODE_NEW = 'new';
	const MODE_UNPUBLISHED = 'unpublished';
	const MODE_PUBLISHED = 'published';
	
	private $_mode;
	
	public function getMode()
	{
		if ($this->_crud->isNew()) {
			$this->_mode = self::MODE_NEW;
		} else if ($this->getValue()) {
			$this->_mode = self::MODE_PUBLISHED;
		} else {
			$this->_mode = self::MODE_UNPUBLISHED;
		}
		return $this->_mode;
	}
	
	public function getMetaId()
	{
		return str_replace('meta_', '', $this->getName());
	}
	
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "hidden";
		if ($this->getMode() === self::MODE_NEW) {
			$attributes['value'] = $this->getDefault();
		} else {
			$attributes['value'] = String::escape($this->getValue());
		}
		return $attributes;
	}
}