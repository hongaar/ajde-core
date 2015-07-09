<?php


namespace Ajde\Filter;

use Ajde\Filter;
use Ajde\Db\Table;
use Ajde\Query;



class LeftJoin extends Filter
{	
	protected $_table;
	protected $_ownerField;
	protected $_childField;
	
	public function __construct($table, $ownerField, $childField)
	{
		$this->_table = $table;
		$this->_ownerField = $ownerField;
		$this->_childField = $childField;
	}
	
	public function prepare(Table $table = null)
	{
		$sql = $this->_table . ' ON ' . $this->_ownerField . ' = ' . $this->_childField;
		return array(
			'join' => array(
				'arguments' => array($sql, Query::JOIN_LEFT),
				'values' => array()
			)
		);
	}
}