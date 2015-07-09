<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field\Enum;
use Ajde\Model;
use Config;
use Ajde\Filter\Where;
use Ajde\Filter;
use Ajde\Filter\WhereGroup;
use Ajde\Query;



class Fk extends Enum
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
	
	/**
	 * 
	 * @return string 
	 */	
	public function getModelName()
	{
		if ($this->hasModelName()) {
			return $this->get('modelName');
		} else {
			$fieldName = $this->getName();
			$model = $this->getCrud()->getModel();
			return (string) $model->getParentModel($fieldName)->getTable();
		}
	}
	
	public function getValue()
	{
		$value = parent::getValue();
		if (!$value instanceof Model && !empty($value)) {
			$model = $this->getModel();
			$model->loadByPK($value);
			$this->set('value', $model);
		}
		return parent::getValue();
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

            $langFilter = false;
            if ($this->hasFilterLang()) {
                $langFilter = $this->getFilterLang();
            }
            $lang = false;

            // Filter lang by parent (model) language
            if ($langFilter == 'parent') {
                $fieldName = $this->getName();
                $parent = $this->getCrud()->getModel();
                if (method_exists($parent, 'getLanguageField')) {
                    $lang = $parent->get($parent->getLanguageField());
                }
            }

            // Filter lang by current (page) language
			if ($langFilter == 'page') {
                $lang = Config::get('lang');
			}

            if ($langFilter && $lang && method_exists($this->_collection, 'getLanguageField')) {
                $languageField = $this->_collection->getLanguageField();
                $this->_collection->addFilter(new Where($languageField, Filter::FILTER_EQUALS, $lang));
            }
		}
		return $this->_collection;
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
		if ($this->hasFilter()) {
			$filter = $this->getFilter();
			$group = new WhereGroup();
			foreach($filter as $rule) {
				$group->addFilter(new Where($this->getModel()->getDisplayField(), Filter::FILTER_EQUALS, $rule, Query::OP_OR));
			}
			$this->getCollection()->addFilter($group);
		}

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
			$this->getCollection()->addFilter($group);
		}

        $this->getCollection()->getQuery()->orderBy = array();
		if ($this->hasOrderBy()) {
			$this->getCollection()->orderBy($this->getOrderBy());
		} else {
			$this->getCollection()->orderBy($this->getModel()->getDisplayField());
		}
		$return = array();
		foreach($this->getCollection() as $model) {
			$fn = 'get' . ucfirst($model->getDisplayField());
			$return[(string) $model] = $model->{$fn}();
		}
		return $return;
	}
}