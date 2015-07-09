<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;
use Ajde\Filter\Where;
use Ajde\Filter;



class Nodelink extends Type
{
    protected $niceName = 'Node link';

	public function getFields()
	{
		$this->required();
		$this->readonly();
		$this->help();
		$this->link();
		$this->defaultValue();
		$this->usePopup();
		return parent::getFields();
	}
	
	public function link()
	{
		$field = $this->fieldFactory('usenodetype');
		$field->setLabel('Node type');
		$field->setType('fk');
		$field->setIsRequired(false);
		$field->setModelName('nodetype');
		$this->addField($field);
	}
	
	public function usePopup()
	{
		$field = $this->fieldFactory('popup');
		$field->setLabel('Choose node from advanced list');
		$field->setType('boolean');
		$this->addField($field);
	}
	
	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);
		$field->setType('fk');
		$field->setModelName('node');
		if ($meta->getOption('usenodetype')) {
			$field->setAdvancedFilter(array(
				new Where('nodetype', Filter::FILTER_EQUALS, $meta->getOption('usenodetype'))
			));
		}
		if ($meta->getOption('popup')) {
			$field->setListRoute('admin/node:view.crud');
			$field->setUsePopupSelector(true);
		}
		return $field;
	}
}