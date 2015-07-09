<?php


namespace Ajde;

use Ajde\Object\Standard;
use Iterator;
use Countable;
use Ajde\Event;
use Ajde\Controller;
use Ajde\Core\Autoloader;
use Ajde\Db;
use Ajde\Query;
use PDO as PDO;
use Ajde\Exception;
use Ajde\Filter\Link;
use Ajde\Filter;
use Ajde\Filter\Where;
use Ajde\Collection\View;
use Ajde\Db\PDOStatement;
use Ajde\Db\PDO as AjdeDbPDO;
use Ajde\Collection as AjdeCollection;



class Collection extends Standard implements Iterator, Countable {
	
	/**
	 * @var string
	 */
	protected $_modelName;
	
	/**
	 * @var PDO
	 */
	protected $_connection;
	
	/**
	 * @var PDOStatement
	 */
	protected $_statement;
	
	/**
	 * @var Ajde_Query
	 */
	protected $_query;
	
	protected $_link = array();
	
	/**
	 * @var Ajde_Db_Table
	 */
	protected $_table;
	
	protected $_filters = array();	
	public $_filterValues = array();
	
	/**
	 * @var Ajde_Collection_View
	 */
	protected $_view;
	
	// For Iterator
	protected $_items = null;
	protected $_position = 0;
	
	private $_sqlInitialized = false;
	private $_queryCount;
	
	public static function register($controller)
	{
		// Extend Ajde_Controller
		if (!Event::has('Ajde_Controller', 'call', 'Ajde_Collection::extendController')) {
			Event::register('Ajde_Controller', 'call', 'Ajde_Collection::extendController');
		}
		// Extend autoloader
		if ($controller instanceof Controller) {
			Autoloader::addDir(MODULE_DIR.$controller->getModule().'/model/');
		} elseif ($controller === '*') {
			self::registerAll();
		} else {
			Autoloader::addDir(MODULE_DIR.$controller.'/model/');
		}		
	}
	
	public static function extendController(Controller $controller, $method, $arguments)
	{
		// Register getCollection($name) function on Ajde_Controller
		if ($method === 'getCollection') {
			return self::getCollection($arguments[0]);
		}
		// TODO: if last triggered in event cueue, throw exception
		// throw new Ajde_Exception("Call to undefined method ".get_class($controller)."::$method()", 90006);
		// Now, we give other callbacks in event cueue chance to return
		return null; 
	}
	
	public static function getCollection($name)
	{
		$collectionName = ucfirst($name) . 'Collection';
		return new $collectionName();
	}
	
	public function __construct()
	{
		$this->_modelName = str_replace('Collection', '', get_class($this)) . 'Model';		
		$this->_connection = Db::getInstance()->getConnection();
		
		$tableNameCC = str_replace('Collection', '', get_class($this));
		$tableName = $this->fromCamelCase($tableNameCC);
		
		$this->_table = Db::getInstance()->getTable($tableName);
		$this->_query = new Query();
	}
	
	public function reset()
	{
		parent::reset();
		$this->_query = new Query();
		$this->_filters = array();	
		$this->_filterValues = array();
		$this->_items = null;
		$this->_position = 0;
		$this->_queryCount = null;
		$this->_sqlInitialized = false;
	}
	
	public function __sleep()
	{
		return array('_modelName', '_items');
	}

	public function __wakeup()
	{
	}
	
	public function rewind() {
		if (!isset($this->_items)) {
    		$this->load();
    	}
        $this->_position = 0;
    }

    public function current() {    	
        return $this->_items[$this->_position];
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        $this->_position++;
    }
	
	public function count($query = false) 
	{
		if ($query == true) {
			if (!isset($this->_queryCount)) {				
				$this->_statement = $this->getConnection()->prepare($this->getCountSql());
				foreach($this->getFilterValues() as $key => $value) {
					if (is_null($value)) {
						$this->_statement->bindValue(":$key", null, PDO::PARAM_NULL);
					} else {
						$this->_statement->bindValue(":$key", $value, PDO::PARAM_STR);
					}
				}
				$this->_statement->execute();
				$result = $this->_statement->fetch(PDO::FETCH_ASSOC);
				$this->_queryCount = $result['count'];
			}
			return $this->_queryCount;
		} else {
			if (!isset($this->_items)) {
				$this->load();
			}
			return count($this->_items);
		}
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return Ajde_Model | boolean 
	 */
	public function find($field, $value) {
		foreach($this as $item) {
			if ($item->{$field} == $value) {
				return $item;
			}
		}
		return false;
	}

    function valid() {
        return isset($this->_items[$this->_position]);
    }
	
	/**
	 * @return Ajde_Db_PDO
	 */
	public function getConnection()
	{
		return $this->_connection;
	}
	
	/**
	 * @return Ajde_Db_Table
	 */
	public function getTable()
	{
		return $this->_table;
	}
	
	/**
	 * @return PDOStatement
	 */
	public function getStatement()
	{
		return $this->_statement;
	}
	
	/**
	 * @return Ajde_Query
	 */
	public function getQuery()
	{
		return $this->_query;
	}
		
	public function populate($array)
	{
		$this->reset();
		$this->_data = $array;
	}
	
	public function getLink($modelName, $value)
	{
		if (!array_key_exists($modelName, $this->_link)) {
			// TODO:
			throw new Exception('Link not defined...');
		}
		return new Link($this, $modelName, $this->_link[$modelName], $value);
	}
	
	// Chainable collection methods
	
	public function addFilter(Filter $filter)
	{
		$this->_filters[] = $filter;
		return $this;		
	}
	
	public function orderBy($field, $direction = Query::ORDER_ASC)
	{
		$this->getQuery()->addOrderBy($field, $direction);
		return $this;
	}
	
	public function limit($count, $start = 0)
	{
		$this->getQuery()->limit((int) $count, (int) $start);
		return $this;
	}
	
	public function filter($field, $value, $comparison = Filter::FILTER_EQUALS, $operator = Query::OP_AND)
	{
		$this->addFilter(new Where($field, $comparison, $value, $operator));
		return $this;
	}
	
	// View functions
	
	public function setView(View $view)
	{
		$this->_view = $view;
	}
		
	/**
	 * @return Ajde_Collection_View
	 */
	public function getView()
	{
		return $this->_view;
	}
	
	/**
	 * @return boolean
	 */
	public function hasView()
	{
		return isset($this->_view) && $this->_view instanceof View;
	}
	
	
	public function applyView(View $view = null)
	{
		if (!$this->hasView() && !isset($view)) {
			// TODO:
			throw new Exception('No view set');
		}
		
		if (isset($view)) {
			$this->setView($view);
		} else {
			$view = $this->getView();
		}
		
		// LIMIT
		$this->limit($view->getPageSize(), $view->getRowStart());
		
		// ORDER BY
		if (!$view->isEmpty('orderBy')) {
			$oldOrderBy = $this->getQuery()->orderBy;
			$this->getQuery()->orderBy = array();
			if (in_array($view->getOrderBy(), $this->getTable()->getFieldNames())) {
				$this->orderBy((string) $this->getTable() . '.' . $view->getOrderBy(), $view->getOrderDir());
			} else {
				// custom column, make sure to add it to the query first!
				$this->orderBy($view->getOrderBy(), $view->getOrderDir());
			}
			foreach($oldOrderBy as $orderBy) {
				$this->orderBy($orderBy['field'], $orderBy['direction']);
			}
		}
		
		// FILTER
		if (!$view->isEmpty('filter')) {
			foreach($view->getFilter() as $fieldName => $filterValue) {
				if ($filterValue != '') {
                    $fieldType = $this->getTable()->getFieldProperties($fieldName, 'type');
                    if ($fieldType == Db::FIELD_TYPE_DATE) {
                        // date fields
                        $start = $filterValue['start'] ? date( 'Y-m-d H:i:s', strtotime($filterValue['start'] . ' 00:00:00')) : false;
                        $end = $filterValue['end'] ? date( 'Y-m-d H:i:s', strtotime($filterValue['end'] . ' 23:59:59')) : false;
                        if ($start) {
                            $this->addFilter(new Where((string) $this->getTable() . '.' . $fieldName, Filter::FILTER_GREATEROREQUAL, $start));
                        }
                        if ($end) {
                            $this->addFilter(new Where((string) $this->getTable() . '.' . $fieldName, Filter::FILTER_LESSOREQUAL, $end));
                        }
                    } else if ($fieldType == Db::FIELD_TYPE_TEXT) {
                        // text fields (fuzzy)
                        $this->addFilter(new Where((string) $this->getTable() . '.' . $fieldName, Filter::FILTER_LIKE, '%' . $filterValue . '%'));
                    } else {
                        // non-date fields (exact match)
                        $this->addFilter(new Where((string) $this->getTable() . '.' . $fieldName, Filter::FILTER_EQUALS, $filterValue));
                    }
				}
			}
		}

		// SEARCH
		if (!$view->isEmpty('search')) {
			$this->addTextFilter($view->getSearch());
		}
	}
	
	public function addTextFilter($text, $operator = Query::OP_AND, $condition = Filter::CONDITION_WHERE)
	{
		$searchFilter = $this->getTextFilterGroup($text, $operator, $condition);
        if ($searchFilter !== false) {
            $this->addFilter($searchFilter);
        } else {
            $this->addFilter(new Where('true', '=', 'false'));
        }
	}
	
	public function getTextFilterGroup($text, $operator = Query::OP_AND, $condition = Filter::CONDITION_WHERE)
	{
        $groupClass = 'Ajde_Filter_' . ucfirst($condition) . 'Group';
        $filterClass = 'Ajde_Filter_' . ucfirst($condition);

		$searchFilter = new $groupClass($operator);
		$fieldOptions = $this->getTable()->getFieldProperties();
		foreach($fieldOptions as $fieldName => $fieldProperties) {
			switch ($fieldProperties['type']) {
				case Db::FIELD_TYPE_TEXT:
				case Db::FIELD_TYPE_ENUM:
                    $searchFilter->addFilter(new $filterClass((string) $this->getTable() . '.' . $fieldName, Filter::FILTER_LIKE, '%' . $text . '%', Query::OP_OR));
                    break;
                case Db::FIELD_TYPE_NUMERIC:
					$searchFilter->addFilter(new $filterClass('CAST(' . (string) $this->getTable() . '.' . $fieldName . ' AS CHAR)', Filter::FILTER_LIKE, '%' . $text . '%', Query::OP_OR));
					break;
				default:
					break;
			}
		}
		return $searchFilter->hasFilters() ? $searchFilter : false;
	}
	
	public function getSql()
	{
		if (!$this->_sqlInitialized) {
			foreach($this->getTable()->getFieldNames() as $field) {
				$this->getQuery()->addSelect((string) $this->getTable() . '.' . $field);
			}
			if (!empty($this->_filters)) {
				foreach($this->getFilter('select') as $select) {
					call_user_func_array(array($this->getQuery(), 'addSelect'), $select);
				}
			}
			$this->getQuery()->addFrom($this->_table);
			if (!empty($this->_filters)) {
				foreach($this->getFilter('join') as $join) {
					call_user_func_array(array($this->getQuery(), 'addJoin'), $join);
				}
				foreach($this->getFilter('where') as $where) {
					call_user_func_array(array($this->getQuery(), 'addWhere'), $where);
				}
                foreach($this->getFilter('having') as $having) {
                    call_user_func_array(array($this->getQuery(), 'addHaving'), $having);
                }
			}
		}
		$this->_sqlInitialized = true;
		return $this->getQuery()->getSql();
	}
	
	public function getCountSql()
	{
		// Make sure to load the filters
		$this->getSql();
		$query = clone $this->getQuery();
		/* @var $query Ajde_Query */
		$query->select = array();
		$query->orderBy = array();
		$query->limit = array('start' => null, 'count' => null);	
		$query->addSelect('COUNT(*) AS count');
		return $query->getSql();
	}
	
	public function getEmulatedSql()
	{
		return PDOStatement::getEmulatedSql($this->getSql(), $this->getFilterValues());
	}
	
	public function getFilter($queryPart)
	{
		$arguments = array();
		foreach($this->_filters as $filter) {
			$prepared = $filter->prepare($this->getTable());			
			if (isset($prepared[$queryPart])) {
				if (isset($prepared[$queryPart]['values'])) {
					$this->_filterValues = array_merge($this->_filterValues, $prepared[$queryPart]['values']);
				}
				$arguments[] = $prepared[$queryPart]['arguments'];
			}			
		}
		if (empty($arguments)) {
		 	return array();
		} else {
			return $arguments; 
		}
	}	
	
	public function getFilterValues()
	{
		return $this->_filterValues;
	}
	
	// Load the collection
	public function load()
	{
		if (!$this->getConnection() instanceof AjdeDbPDO) {
			// return false;
		}
		$this->_statement = $this->getConnection()->prepare($this->getSql());
		foreach($this->getFilterValues() as $key => $value) {
			if (is_null($value)) {
				$this->_statement->bindValue(":$key", null, PDO::PARAM_NULL);
			} else {
				$this->_statement->bindValue(":$key", $value, PDO::PARAM_STR);
			}
		}
		$this->_statement->execute();
		return $this->_items = $this->_statement->fetchAll(PDO::FETCH_CLASS, $this->_modelName);
	}
	
	public function loadParents()
	{
		if (count($this) > 0) {
			foreach($this as $model) {
				$model->loadParents();
			}
		}
	}
	
	public function length()
	{
		if (!isset($this->_items)) {
			$this->load();
		}
		return count($this->_items);
	}
	
	public function hash()
	{
		$str = '';
        /** @var $item Ajde_Model */
        foreach($this as $item) {
			$str .= implode('', $item->valuesAsSingleDimensionArray());
		}
		return md5($str);
	}
	
	public function toArray()
	{
		$array = array();
		foreach($this as $item) {
			$array[] = $item->values();
		}
		return $array;
	}
	
	public function items()
	{
		if (!isset($this->_items)) {
			$this->load();
		}
		return $this->_items;
	}

    public function add($item)
    {
        $this->_items[] = $item;
    }

    public function merge(AjdeCollection $collection)
    {
        foreach($collection as $item)
        {
            $this->add($item);
        }
    }

    public function deleteAll()
    {
        foreach($this as $item) {
            $item->delete();
        }
    }
}