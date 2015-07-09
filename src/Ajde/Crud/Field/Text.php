<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Text extends Field
{
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "text";
		$attributes['value'] = String::escape($this->getValue());
		$attributes['maxlength'] = String::escape($this->getLength());
		if ($this->hasReadonly() && $this->getReadonly() === true) {
			$attributes['readonly'] = "readonly";
		}
		if ($this->hasEmphasis() && $this->getEmphasis() === true) {
			$attributes['class'] = "emphasis";
		}
		if ($this->hasPlaceholder()) {
			$attributes['placeholder'] = $this->getPlaceholder();
		}
		return $attributes;		
	}
}