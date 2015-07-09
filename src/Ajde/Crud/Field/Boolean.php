<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;



class Boolean extends Field
{
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "hidden";
		$attributes['value'] = $this->getValue() ? '1' : '0';
		return $attributes;
	}	
	
	public function getHtmlList($value = null)
	{
		$value = issetor($value, $this->hasValue() ? $this->getValue() : false);
		return $value ?
			"<i class='icon-ok' title='Yes'></i>" :
			"<i class='icon-remove' title='No'></i>";
			
	}
}