<?php


namespace Ajde\Model\Validator;

use Ajde\Model\ValidatorAbstract;



class Enum extends ValidatorAbstract
{
	protected function _validate()
	{
		return array('valid' => true);
	}
}