<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Timespan extends Field
{
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "hidden";
		$attributes['value'] = String::escape($this->getValue());
		if ($this->hasReadonly() && $this->getReadonly() === true) {
			$attributes['readonly'] = 'readonly';	
		}	
		return $attributes;		
	}
}