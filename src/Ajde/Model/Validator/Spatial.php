<?php


namespace Ajde\Model\Validator;

use Ajde\Model\ValidatorAbstract;



class Spatial extends ValidatorAbstract
{
	protected function _validate()
	{
        $trimmed = trim($this->_value);
        if ($this->getIsRequired() && empty($trimmed)) {
			return array('valid' => false, 'error' => __('Required field'));
		}
		return array('valid' => true);
	}
}