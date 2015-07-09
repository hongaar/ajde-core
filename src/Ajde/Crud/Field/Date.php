<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Date extends Field
{
	protected function prepare()
	{
		if (
				((!$this->hasValue() || $this->isEmpty('value')) && $this->getDefault() == 'CURRENT_TIMESTAMP') ||
				($this->getIsAutoUpdate())
			) {
			$this->setReadonly(true);
		}
	}

	protected function _getHtmlAttributes()
	{
		$attributes = array();
		if ($this->hasReadonly() && $this->getReadonly() === true) {
			$attributes['value'] = String::escape( $this->getValue() );
			$attributes['type'] = "text";
			$attributes['readonly'] = "readonly";
		} else {
			if ($this->getValue()) {
				$attributes['value'] = String::escape( date('Y-m-d', strtotime($this->getValue()) ) );
			}			
			$attributes['type'] = "date";
		}
		return $attributes;
	}
}
