<?php


namespace Ajde\Model\Validator;

use Ajde\Model\ValidatorAbstract;



class Date extends ValidatorAbstract
{
	protected function _validate()
	{
		return array('valid' => true);
	}
}