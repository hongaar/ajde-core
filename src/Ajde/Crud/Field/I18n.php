<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field\Enum;
use Ajde\Crud;
use Config;
use Ajde\Lang;



class I18n extends Enum
{
	protected $_useSpan = false;

	public function __construct(Crud $crud, $fieldOptions) {
		parent::__construct($crud, $fieldOptions);
		$this->set('default', Config::get('lang'));
	}

	public function getFieldsToClone()
	{
		return ($this->has('cloneFields') ? (array) $this->get('cloneFields') : array());
	}

	public function getValues()
	{
		$lang = Lang::getInstance();
		$langs = $lang->getAvailableNiceNames();
		return $langs;
	}

	public function getAvailableTranslations()
	{
		$lang = Lang::getInstance();
		$langs = $lang->getAvailableNiceNames();

		$model = $this->_crud->getModel();
		/* @var $model Ajde_Lang_Proxy_Model */
		$translations = $model->getTranslations();

		$translatedLangs = array();
		foreach ($translations as $model) {
			/* @var $model Ajde_Lang_Proxy_Model */
			$modelLanguage = $model->getLanguage();
			if (!empty($modelLanguage)) {
				$translatedLangs[$modelLanguage] = $model;
            }
		}

		foreach($langs as $key => &$name) {
			$name = array(
					'name' => $name,
				);

			if (array_key_exists($key, $translatedLangs)) {
				$name['model'] = $translatedLangs[$key];
			}
		}

		return $langs;
	}

	public function _getHtmlAttributes() {
		$attributes = array();
		$attributes['class'] = 'lang';
		return $attributes;
	}
}