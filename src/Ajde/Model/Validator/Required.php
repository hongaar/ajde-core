<?php


namespace Ajde\Model\Validator;

use Ajde\Model\ValidatorAbstract;



class Required extends ValidatorAbstract
{
	protected function _validate()
	{
		if (empty($this->_value)) {
			if (!$this->getIsAutoIncrement()) {
				return array('valid' => false, 'error' => __('Required field'));
			}
		}
		return array('valid' => true);
	}
}