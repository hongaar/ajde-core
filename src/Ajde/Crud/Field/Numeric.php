<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Numeric extends Field
{
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "number";
		$attributes['step'] = "any";
		$attributes['value'] = String::escape($this->getValue());
		$attributes['maxlength'] = $this->getLength();
		if ($this->getIsAutoIncrement() === true || ($this->hasReadonly() && $this->getReadonly() === true)) {
			$attributes['readonly'] = "readonly";
		}
			
		return $attributes;		
	}
}