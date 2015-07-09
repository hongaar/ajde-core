<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;



class Spatial extends Field
{
	protected function _getHtmlAttributes()
	{
		$value = $this->getValue();
		if (!substr_count($value, ' ') && !empty($value)) {
			$data = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $this->getValue());
			$value = str_replace(',','.',$data['lat']).' '.str_replace(',','.',$data['lon']);
		}
		$attributes = array();
		$attributes['type'] = "hidden";
		$attributes['value'] = $value;
		return $attributes;		
	}
}