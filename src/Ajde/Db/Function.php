<?php


namespace Ajde\Db;




class Function
{
	private $_function = null;
	
	public function __construct($functionName)
	{
		$this->_function = $functionName;
	}
	
	public function __toString()
	{
		return $this->_function;
	}
}