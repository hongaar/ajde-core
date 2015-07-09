<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;



class Time extends Field
{	
	
	protected function _getHtmlAttributes()
	{		
		$attributes = array();
		$attributes['type'] = 'time';
        if ($this->getValue()) {
		    $attributes['value'] = String::escape( date('H:i', strtotime($this->getValue()) ) );
        } else {
            $attributes['value'] = '';
        }
		if ($this->hasReadonly() && $this->getReadonly() === true) {
			$attributes['readonly'] = 'readonly';	
		}		
		return $attributes;		
	}
}