<?php


namespace Ajde;

use Ajde\Object\Standard;
use Ajde\Model;
use Ajde\Crud\Options;
use Exception as Exception;
use Ajde\Exception\Handler;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Crud\Field\Sort;
use Ajde\Crud\Field\Boolean;
use Ajde\Crud\Field\File;
use Config;
use Ajde\Http\Response;
use Ajde\Event\Dispatcher;
use Ajde\Core\ExternalLibs;
use Ajde\Core\Exception as AjdeException;
use Ajde\Session;
use Ajde\Collection\View;
use \Ajde;
use Ajde\Template;



class Crud extends Standard
{
	protected $_model = null;
	protected $_collection = null;
	
	protected $_fields = null;
	
	//protected $_operation = null;
	protected $_operation = 'list';

    protected $_templateData = array();

	public function __construct($model, $options = array()) {
		if ($model instanceof Model) {
			$this->_model = $model;
		} else {
			$modelName = $this->toCamelCase($model, true) . 'Model';
			$this->_model = new $modelName();
		}
		if ($options instanceof Options) {
			$options = $options->getArray();
		}
		$this->setOptions($options);
	}
	
	public function __toString()
	{
		try {
			$output = $this->output();
		} catch (Exception $e) {
			$output = Handler::handler($e);
		}
		return $output;
	}
	
	public function output()
	{
		$controller = Controller::fromRoute(new Route('_core/crud:' . $this->getOperation()));
		$controller->setCrudInstance($this);
		return $controller->invoke();
	}

    public function export($format = 'excel')
    {
        $exporterClass = 'Ajde_Crud_Export_' . ucfirst($format);
        $exporter = new $exporterClass;
        /* @var $exporter Ajde_Crud_Export_Interface */

        $table = array();

        $fieldsToShow = $this->getFieldNames();

        $headers = array();

        $this->getCollection()->getView()->setPageSize(9999999999);

        $items = $this->getItems();

        foreach($fieldsToShow as $fieldName) {

            // JSON
            if ($items->count() && ($first = current($items->items())) && $first->isFieldJson($fieldName)) {
                $maxJsonFields = 0;
                $useJsonFields = [];
                foreach ($items as $model) {
                    $jsonFields = (array)@json_decode($model->has($fieldName) ? $model->get($fieldName) : '');
                    if (count($jsonFields) > $maxJsonFields) {
                        $useJsonFields = array_keys($jsonFields);
                        $maxJsonFields = count($jsonFields);
                    }
                }
                foreach ($useJsonFields as $key) {
                    $headers[] = $key;
                }
            // Normal
            } else {
                $field = $this->getField($fieldName);
                $headers[] = $field->getLabel();
            }
        }

        $table[] = $headers;

        foreach($items as $model) {
		    /* @var $model Ajde_Model */
		    $this->fireCrudLoadedOnModel($model);

            $row = array();

            foreach($fieldsToShow as $fieldName) {

                $field = $this->getField($fieldName);
                $value = $model->has($fieldName) ? $model->get($fieldName) : false;

                // JSON
                if ($model->isFieldJson($fieldName)) {
                    foreach(($jsonFields = (array) @json_decode($value)) as $item) {
                        $row[] = $item;
                    }
                    for($i = 0; $i < ($maxJsonFields - count($jsonFields)); $i++) {
                        $row[] = '';
                    }
                } else if ($this->getField($fieldName) instanceof Sort) {
                    $row[] = $value;
                // Display function
//                } elseif ($field->hasFunction() && $field->getFunction()) {
//                    $displayFunction = $field->getFunction();
//                    $displayFunctionArgs = $field->hasFunctionArgs() ? $field->getFunctionArgs() : array();
//                    $funcValue = call_user_func_array(array($model, $displayFunction), $displayFunctionArgs);
//                    $row[] = $funcValue;
                // Linked Model (not loaded)
                } elseif ($value instanceof Model && !$value->hasLoaded()) {
                    $row[] = "(not set)";
                // Linked Model
                } elseif ($value instanceof Model && $value->hasLoaded()) {
                    $row[] = $value->get($value->getDisplayField());
                // Boolean
                } elseif ($field instanceof Boolean) {
                    $row[] = $value;
                // File
                } elseif ($this->getField($fieldName) instanceof File) {
                    $row[] = Config::get('site_root') . $field->getSaveDir() . $value;
                // Text value
                } else {
                    $row[] = strip_tags($value);
                }

            }

            $table[] = $row;
        }

        $exporter->prepare($this->getSessionName(), $table);
        return $exporter;
    }
	
	/**
	 * GETTERS & SETTERS 
	 */
	
	/**
	 *
	 * @return string
	 */
	public function getAction()
	{
		if (!$this->hasAction()) {
			$this->setAction('list');
		}
		return parent::getAction();
	}
	
	public function setAction($value)
	{
		if (substr($value, 0, 4) === 'edit' || substr($value, 0, 4) === 'list') {
			$this->setOperation(substr($value, 0, 4));
		} 
		parent::setAction($value);
	}
	
	public function setOperation($operation)
	{
		$this->_operation = $operation;
	}
	
	public function getOperation()
	{
//		if (!isset($this->_operation)) {
//			if (Ajde::app()->getRequest()->has('new')) {
//				$this->setOperation('new'); 
//			} else if (Ajde::app()->getRequest()->has('edit')) {
//				$this->setOperation('edit'); 
//			} else {
//				$this->setOperation('list'); 
//			}
//		}
		return $this->_operation;
	}

    /**
     * OPTIONS
     * @param $name
     * @param bool|mixed $default
     * @return array|bool
     */
	
	public function getOption($name, $default = false)
	{
		$path = explode('.', $name);
		$options = $this->getOptions();
		foreach($path as $key) {
			if (isset($options[$key])) {
				$options = $options[$key];
			} else {
				return $default;
			}
		}
		return $options;
	}
	
	public function setOption($name, $value)
	{
		$path = explode('.', $name);
		$options = $this->getOptions();
		$wc = &$options;
		foreach($path as $key) {
			if (!isset($wc[$key])) {
				$wc[$key] = array();
			}
			$wc = &$wc[$key];
		}
		$wc = $value;
		$this->setOptions($options);
	}
		
	/**
	 *
	 * @return array
	 */
	public function getOptions($key = null)
	{
		if (isset($key)) {
			$options = parent::getOptions();	
			return issetor($options[$key], array());
		} else {
			return parent::getOptions();
		}
	}
	
	public function setOptions($value)
	{
		parent::setOptions($value);
	}
	
	/**
	 * MISC 
	 */
	
	public function setItem($value)
	{
		parent::setItem($value);
	}
	
	public function setCustomTemplateModule($value)
	{
		parent::setCustomTemplateModule($value);
	}
	
	public function getCustomTemplateModule()
	{
		if (parent::hasCustomTemplateModule()) {
			return parent::getCustomTemplateModule();
		}
		return (string) $this->getModel()->getTable();		
	}
	
	public function setEditAction($value)
	{
		parent::setEditAction($value);
	}
	
	public function getEditAction()
	{
		if (parent::hasEditAction()) return parent::getEditAction();
		return false;
	}
	
	public function setNewAction($value)
	{
		parent::setNewAction($value);
	}
	
	public function getNewAction()
	{
		if (parent::hasNewAction()) return parent::getNewAction();
		return false;
	}
	
	public function setListAction($value)
	{
		parent::setListAction($value);
	}
	
	public function getListAction()
	{
		if (parent::hasListAction()) return parent::getListAction();
		return false;
	}
	
	
	/**
	 * @return Ajde_Collection
	 */
	public function getCollection()
	{
		if (!isset($this->_collection))	{
			$collectionName = str_replace('Model', '', get_class($this->getModel())) . 'Collection';
			$this->_collection = new $collectionName();
		}
		return $this->_collection;
	}
	
	/**
	 * @return Ajde_Model
	 */
	public function getModel()
	{
		return $this->_model;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getHash()
	{
		return spl_object_hash($this);
	}	
	
	/**
	 * HELPERS
	 */
	
	public function loadItem($id = null)
	{
		$model = $this->getModel();
		if (isset($id)) {
			$model->loadByPK($id);
		}
		$this->setItem($model);
		return $this->getItem();
	}

	/**
	 *
	 * @return Ajde_Model 
	 */
	public function getItem()
	{
		if ($this->isNew()) {
			$this->fireCrudLoadedOnModel($this->getModel());
			return $this->getModel();
		}
		if (!$this->getModel()->getPK()) {
			$model = $this->getModel();
			if (!$model->loadByPK($this->getId())) {
				Response::redirectNotFound();
			} else {
				if (!$model->getAutoloadParents()) {
					$model->loadParents();
				}
			}
			$this->fireCrudLoadedOnModel($this->getModel());
		}
		return $this->getModel();
	}
	
	public function isNew()
	{
		return (!$this->hasId() || $this->getId() === false || is_null($this->getId()));
	}
	
	/**
	 *
	 * @return Ajde_Collection
	 */
	public function getItems()
	{
		$collection = $this->getCollection();
		
		// Collection view
		if ($collection->hasView()) {
			$collection->applyView();
		}
		
		$collection->load();
		
		// TODO: this should be done with JOIN
		if ($this->getModel()->getAutoloadParents()) {
			$collection->loadParents();
		}		
		return $collection;
	}
	
	public function fireCrudLoadedOnModel($model)
	{
		Dispatcher::trigger($model, 'afterCrudLoaded');
	}
	
	public function getFields()
	{
		if (!isset($this->_fields)) {	
			$this->loadFields();
		}
		return $this->_fields;
	}

    public function setReadOnlyForAllFields()
    {
        foreach($this->getModel()->getTable()->getFieldNames() as $fieldName) {
            $this->setOption('fields.' . $fieldName . '.readonly', true);
        }
    }
	
	public function loadFields()
	{
		$fields = array();
		$allFields = $this->getDeclaredFieldNames();
		
		$fieldsArray =  $this->getModel()->getTable()->getFieldProperties();		
		// TODO: changed getItem to getModel, any side effects?
		$parents = $this->getModel()->getTable()->getParents();
		
		foreach($allFields as $fieldName) {
			$fieldProperties = array();
			if (isset($fieldsArray[$fieldName])) {
				$fieldProperties = $fieldsArray[$fieldName];
			}
			$fieldOptions = $this->getFieldOptions($fieldName, $fieldProperties);		
			$fieldOptions['name'] = $fieldName;
			if (in_array($fieldOptions['name'], $parents)) {
				$fieldOptions['type'] = 'fk';
			}
			$field = $this->createField($fieldOptions);
			$fields[$fieldName] = $field;
		}
		return $this->_fields = $fields;
	}
	
	public function createField($fieldOptions)
	{		
		if (!isset($fieldOptions['type'])) {
			$fieldOptions['type'] = 'text';
		}
		$fieldClass = ExternalLibs::getClassname("Ajde_Crud_Field_" . ucfirst($fieldOptions['type']));				
		$field = new $fieldClass($this, $fieldOptions);
		if ($this->getOperation() === 'edit') {					
			if (!$field->hasValue() || $field->hasEmpty('value')) {
				if ($this->isNew() && $field->hasNotEmpty('default')) {
					$field->setValue($field->getDefault());
				} elseif (!$this->isNew() && $this->getItem()->has($field->getName())) {
					$field->setValue($this->getItem()->get($field->getName()));
				} else {
					$field->setValue(false);
				}
			}
		}		
		return $field;
	}
	
	/**
	 *
	 * @param string $fieldName
	 * @return Ajde_Crud_Field
	 * @throws Ajde_Exception 
	 */
	public function getField($fieldName, $strict = true)
	{
		if (!isset($this->_fields)) {
			$this->getFields();
		}
		if (isset($this->_fields[$fieldName])) {
			return $this->_fields[$fieldName];
		} else {
			if ($strict === true) {
				// TODO:
				throw new AjdeException($fieldName . ' is not a field in ' . (string) $this->getModel()->getTable());
			} else {
				return false;
			}
		}
	}
	
	public function getDeclaredFieldNames()
	{
		$fields = $this->getFieldNames();
		foreach($this->getFieldNamesFromOptions() as $optionField) {
			if (!in_array($optionField, $fields)) {
				$fields[] = $optionField;
			}
		}
		return $fields;
	}
	
	public function getFieldNamesFromOptions()
	{
		return array_keys($this->getOptions('fields'));
	}
	
	public function getFieldOptions($fieldName, $fieldProperties = array())
	{
		$fieldsOptions = $this->getOptions('fields');
		$fieldOptions = issetor($fieldsOptions[$fieldName], array());
		return array_merge($fieldProperties, $fieldOptions);
	}
	
	public function getFieldNames()
	{
		$model = $this->getModel();
		return $model->getTable()->getFieldNames();
	}
	
	public function getFieldLabels()
	{
		$model = $this->getModel();
		return $model->getTable()->getFieldLabels();
	}
	
	public function setSessionName($name)
	{
		parent::setSessionName($name);
	}
	
	public function getSessionName()
	{
		if (parent::hasSessionName()) {
			return parent::getSessionName();
		} else {
			return (string) $this->getModel()->getTable();
		}
	}
	
	/**
	 * 
	 * @param array $viewParams
     * @param boolean|string $persist
	 * @return Ajde_Collection_View
	 */
	public function getCollectionView($viewParams = array(), $persist = 'auto')
	{
		if (!$this->getCollection()->hasView()) {
			$viewSession = new Session('AC.Crud.View');
			$sessionName = $this->getSessionName();

            if ($viewSession->has($sessionName)) {
				$crudView = $viewSession->get($sessionName);
			} else {
				$crudView = new View($sessionName, $this->getOption('list.view', array()));
                $crudView->setColumns($this->getOption('list.show', $this->getFieldNames()));
			}

            // somehow, when altering crudView, the instance in the session gets updated as well, and we don't want that
            $crudView = clone $crudView;

			if (empty($viewParams)) {
				$viewParams = Ajde::app()->getRequest()->getParam('view', array());
                // if we have params, but no columns, assume a reset
                if (!empty($viewParams) && !isset($viewParams['columns'])) {
                    $viewParams['columns'] = $this->getOption('list.show', $this->getFieldNames());
                }
			}
			$crudView->setOptions($viewParams);

            if (($persist == 'auto' && $this->getOperation() == 'list') || $persist === true) {
			    $viewSession->set($sessionName, $crudView);
            }

			$this->getCollection()->setView($crudView);
		}
		return $this->getCollection()->getView();
	}
		
	/**
	 * RENDERING
	 */
	
	public function getTemplate()
	{
        $template = new Template(MODULE_DIR . '_core/', 'crud/' . $this->getOperation());
		Ajde::app()->getDocument()->autoAddResources($template);
		if ($this->getOperation() !== $this->getAction()) {
			$template = new Template(MODULE_DIR . '_core/', 'crud/' . $this->getAction());
		}
		if ($this->_hasCustomTemplate()) {			
			$base = $this->_getCustomTemplateBase();
			$action = $this->_getCustomTemplateAction();
            $template = new Template($base, $action);
		}
        $template->assignArray($this->_templateData);
		return $template;
	}

    public function setTemplateData($array)
    {
        $this->_templateData = (array) $array;
    }
		
	private function _hasCustomTemplate()
	{
		$base = $this->_getCustomTemplateBase();
		$action = $this->_getCustomTemplateAction();
		return Template::exist($base, $action) !== false;
	}
	
	private function _getCustomTemplateBase()
	{
		return MODULE_DIR . $this->getCustomTemplateModule() . DIRECTORY_SEPARATOR;
	}
	
	private function _getCustomTemplateAction()
	{
		return 'crud/' . (string) $this->getModel()->getTable() . DIRECTORY_SEPARATOR . $this->getAction();
	}
}