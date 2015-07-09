<?php


namespace Ajde\Filter;

use Ajde\Filter\Where;
use Ajde\Db\Table;
use Ajde\Db\Function;



class Having extends Where
{	
	protected $_field;
	protected $_comparison;
	protected $_value;
	protected $_operator;
	
	public function prepare(Table $table = null)
	{
		$values = array();
		if ($this->_value instanceof Function) {
			$sql = $this->_field . $this->_comparison . (string) $this->_value;
		} else {
			$sql = $this->_field . $this->_comparison . ':' . spl_object_hash($this);
			$values = array(spl_object_hash($this) => $this->_value);
		}
		return array(
			'having' => array(
				'arguments' => array($sql, $this->_operator),
				'values' => $values
			)
		);
	}
}