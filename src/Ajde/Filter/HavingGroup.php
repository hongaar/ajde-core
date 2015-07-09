<?php


namespace Ajde\Filter;

use Ajde\Filter;
use Ajde\Query;
use Ajde\Db\Table;



class HavingGroup extends Filter
{		
	protected $_filters;
	protected $_operator;
	
	public function __construct($operator = Query::OP_AND)
	{
		$this->_operator = $operator;
	}
	
	public function addFilter(Filter $filter)
	{
		$this->_filters[] = $filter;
	}
    
    public function hasFilters()
    {
        return !empty($this->_filters);
    }
	
	public function prepare(Table $table = null)
	{
		$sqlWhere = '';
		$first = true;
		$values = array();
		foreach($this->_filters as $filter) {
			$prepared = $filter->prepare($table);
			foreach($prepared as $queryPart => $v) {
				switch ($queryPart) {
					case 'having':
						if ($first === false) {
							$sqlWhere .= ' ' . $v['arguments'][1];
						}
						$sqlWhere .= ' ' . $v['arguments'][0];
						$first = false;
						if (isset($v['values'])) {
							$values = array_merge($values, $v['values']);
						}
						break;
				}
			}				
		}
		
		if (!$sqlWhere) {
			$sqlWhere = 1;
		}
		
		return array(
			'having' => array(
				'arguments' => array('(' . $sqlWhere . ')', $this->_operator),
				'values' => $values
			)
		);
	}
}