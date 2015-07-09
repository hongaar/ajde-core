<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Filter\WhereGroup;
use Ajde\Filter\Where;
use Ajde\Filter;
use Ajde\Query;
use Ajde\Filter\Join;



class Multiple extends Field
{
	/**
	 *
	 * @var Ajde_Collection
	 */
	private $_collection;
	
	/**
	 *
	 * @var Ajde_Model
	 */
	private $_model;

    protected function _getHtmlAttributes()
    {
        $attributes = array();
        $attributes['type'] = "hidden";
        return $attributes;
    }

    /**
	 * 
	 * @return string 
	 */	
	public function getModelName()
	{
		if ($this->hasModelName()) {
			return $this->get('modelName');
		} else {
			return $this->getName();
		}
	}
	
	/**
	 * 
	 * @return string 
	 */	
	public function getParentName()
	{
		if ($this->hasParent()) {
			return $this->get('parent');
		} else {
			return (string) $this->_crud->getModel()->getTable();
		}
	}
	
	/**
	 *
	 * @return Ajde_Collection
	 */
	public function getCollection()
	{
		if (!isset($this->_collection)) {
			$collectionName = ucfirst($this->getModelName()) . 'Collection';
			$this->_collection = new $collectionName;
		}
		return $this->_collection;
	}

	public function getSortField()
	{
		if ($this->hasTableFields()) {
			foreach ($this->getTableFields() as $extraField) { 
				if ($extraField['type'] == 'sort') {
					return $extraField['name'];
				}
			}
		}
		return false;
	}
	
	public function getSortBy()
	{
		if ($this->has('sortBy')) {
			return $this->get('sortBy');
		}
		return false;
	}
	
	/**
	 *
	 * @return Ajde_Model 
	 */
	public function getModel()
	{
		if (!isset($this->_model)) {
			$modelName = ucfirst($this->getModelName()) . 'Model';
			$this->_model = new $modelName;
		}
		return $this->_model;
	}
	
	public function getValues()
	{
        $collection = $this->getCollection();
        $collection->reset();
        
		if ($this->hasFilter()) {
			$filter = $this->getFilter();
			$group = new WhereGroup();
			foreach($filter as $rule) {
				$group->addFilter(new Where($this->getModel()->getDisplayField(), Filter::FILTER_EQUALS, $rule, Query::OP_OR));
			}
			$collection->addFilter($group);
		}

		if ($this->hasAdvancedFilter()) {
			$filters = $this->getAdvancedFilter();
			$group = new WhereGroup();
			foreach($filters as $filter) {
				if ($filter instanceof Where) {
					$group->addFilter($filter);
				} else {
					$collection->addFilter($filter);
				}		
			}
			$collection->addFilter($group);
		}
		
		$collection->orderBy($this->getModel()->getDisplayField());
//		$return = array();
//		foreach($this->getCollection() as $model) {
//			$return[(string) $model] = $model->get($model->getDisplayField());
//		}
//		return $return;
		return $collection;
	}
	
	public function getChildren()
	{		
		if ($this->hasCrossReferenceTable()) {
			$childPk = $this->getModel()->getTable()->getPK();
			$parent = (string) $this->getCrud()->getModel()->getTable();
			$parentId = $this->getCrud()->getModel()->getPK();			
			$crossReferenceTable = $this->getCrossReferenceTable();
			$childField = $this->has('childField') ? $this->get('childField') : $this->getModelName();
			
			// TODO: implement $this->getAdvancedFilter() filters in subquery
			
			$collection = $this->getCollection();
			$collection->reset();
		
			//$subQuery = new Ajde_Db_Function('(SELECT ' . $this->getModelName() . ' FROM ' . $crossReferenceTable . ' WHERE ' . $parent . ' = ' . (integer) $parentId . $orderBy . ')');
			//$collection->addFilter(new Ajde_Filter_Where($childPk, Ajde_Filter::FILTER_IN, $subQuery));
			
			$collection->getQuery()->addSelect($crossReferenceTable . '.id AS crossId');
			$collection->addFilter(new Join($crossReferenceTable, $crossReferenceTable.'.'.$childField, $this->getModelName().'.'.$childPk));
			$collection->addFilter(new Where($crossReferenceTable.'.'.$parent, Filter::FILTER_EQUALS, (integer) $parentId));
			
			if ($this->getSortField()) {
				$collection->orderBy($crossReferenceTable . '.' . $this->getSortField());
			}

            if ($this->hasCrossRefConstraints()) {
                $constraints = $this->getCrossRefConstraints();
                $group = new WhereGroup();
                foreach($constraints as $k => $v) {
                    $group->addFilter(new Where($k, Filter::FILTER_EQUALS, $v));
                }
                $collection->addFilter($group);
            }
			
//			echo $collection->getEmulatedSql();

		} else {
			$collection = $this->getCollection();
			$collection->addFilter(new Where($this->getParentName(), Filter::FILTER_EQUALS, (string) $this->_crud->getModel()));
			
			if ($this->hasAdvancedFilter()) {
				$filters = $this->getAdvancedFilter();
				$group = new WhereGroup();
				foreach($filters as $filter) {
					if ($filter instanceof Where) {
						$group->addFilter($filter);
					} else {
						$this->getCollection()->addFilter($filter);
					}		
				}
				$collection->addFilter($group);
			}
			
			if ($this->getSortField()) {
				$collection->orderBy($this->getSortField());
			}
			
//			echo $collection->getEmulatedSql();
			
		}
		return $collection;
	}
}