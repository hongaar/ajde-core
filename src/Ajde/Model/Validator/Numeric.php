<?php


namespace Ajde\Model\Validator;

use Ajde\Model\ValidatorAbstract;



class Numeric extends ValidatorAbstract
{
	protected function _validate()
	{
        if (!empty($this->_value)) {            
			if (!is_numeric($this->_value)) {
                return array( 'valid' => false, 'error' => __('Not a number') );
			}
		}
		return array('valid' => true);
	}
}