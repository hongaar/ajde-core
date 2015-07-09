<?php


namespace Ajde\Model;

use Ajde\Object\Standard;
use Ajde\Model;
use Ajde\Db;
use Ajde\Model\Validator\Date;
use Ajde\Model\Validator\Numeric;
use Ajde\Model\Validator\Text;
use Ajde\Model\Validator\Enum;
use Ajde\Model\Validator\Spatial;
use Ajde\Model\Validator\Required;
use Ajde\Model\Validator\Unique;



class Validator extends Standard
{	
	/**
	 *
	 * @var Ajde_Model
	 */
	protected $_model = null;
	protected $_errors = null;
	
	public function __construct(Model $model)
	{
		$this->_model = $model;
	}
	
	private function _initValidators($fieldOptions)
	{
		foreach($fieldOptions as $fieldName => $fieldProperties) {
			switch (issetor($fieldProperties['type'])) {
				case Db::FIELD_TYPE_DATE:				
					$this->_model->addValidator($fieldName, new Date());
					break;
                case 'sort':
                case Db::FIELD_TYPE_NUMERIC:
					$this->_model->addValidator($fieldName, new Numeric());
					break;
				case Db::FIELD_TYPE_TEXT:
					$this->_model->addValidator($fieldName, new Text());
					break;
				case Db::FIELD_TYPE_ENUM:
					$this->_model->addValidator($fieldName, new Enum());
					break;
				case Db::FIELD_TYPE_SPATIAL:
					$this->_model->addValidator($fieldName, new Spatial());
					break;
				default :
					break;
			}
			
			if (issetor($fieldProperties['isRequired']) === true && issetor($fieldProperties['default']) == '') {
				$this->_model->addValidator($fieldName, new Required());
			}
			
			if (issetor($fieldProperties['isUnique']) === true) {
				$this->_model->addValidator($fieldName, new Unique());
			}
		}
	}
	
	public function shouldValidateDynamicField($fieldOptions)
	{
		if (isset($fieldOptions['showOnlyWhen'])) {
			$showOnlyWhens = $fieldOptions['showOnlyWhen'];
			foreach($showOnlyWhens as $fieldName => $showWhenValues) {
				$value = -1;
				if ($this->_model->has($fieldName)) {
					$value = strtolower(str_replace(' ', '', $this->_model->get($fieldName)));
				}
				if (in_array($value, $showWhenValues)) {
					return true;
				}
			}
			return false;
		}
		return true;
	}
	
	public function validate($options = array())
	{
		$fieldsArray = $this->_model->getTable()->getFieldProperties();
		$fieldOptions = array();

		// Add all model fields
		foreach($fieldsArray as $fieldName => $fieldProperties) {
			$fieldOptions[$fieldName] = array_merge($fieldProperties, isset($options[$fieldName]) ? $options[$fieldName] : array());
			if (isset($options[$fieldName])) {
				unset($options[$fieldName]);
			}
		}
		
		// Add all non-model fields
		foreach($options as $fieldName => $fieldProperties) {
			$fieldOptions[$fieldName] = $fieldProperties;
		}
		
		$valid = true;
		$errors = array();
		$this->_initValidators($fieldOptions);
		
		foreach($this->_model->getValidators() as $fieldName => $fieldValidators) {
			foreach($fieldValidators as $fieldValidator) {
				/* @var $fieldValidator Ajde_Model_ValidatorAbstract */
				$value = null;
				if ($this->_model->has($fieldName)) {
					$value = $this->_model->get($fieldName);
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    } else {
                        $value = (string) $value;
                    }
				}
				// Only validate when dynamic field is shown
				if ($this->shouldValidateDynamicField($fieldOptions[$fieldName])) {
					$result = $fieldValidator->validate($fieldOptions[$fieldName], $value);				
					if ($result['valid'] === false) {
						if (!isset($errors[$fieldName])) {
							$errors[$fieldName] = array();
						}
						$errors[$fieldName][] = $result['error'];
						$valid = false;
					}
				}
			}
		}
		$this->_errors = $errors;
		return $valid;
	}
	
	public function getErrors()
	{
		return $this->_errors;
	}
}