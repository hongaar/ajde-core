<?php


namespace Ajde\Filter;

use Ajde\Filter;
use Ajde\Query;
use Ajde\Db\Table;
use Ajde\Db\Function;



class Where extends Filter
{	
	protected $_field;
	protected $_comparison;
	protected $_value;
	protected $_operator;
	
	public function __construct($field, $comparison, $value, $operator = Query::OP_AND)
	{
		$this->_field = $field;
		$this->_comparison = $comparison;
		$this->_value = $value;
		$this->_operator = $operator;
	}
	
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
			'where' => array(
				'arguments' => array($sql, $this->_operator),
				'values' => $values
			)
		);
	}
}