<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;



class Listofoptions extends Type
{
    protected $niceName = 'List of options';

	public function getFields()
	{
		$this->required();
		$this->readonly();
		$this->help();
		$this->options();
		$this->defaultValue();
		return parent::getFields();
	}
	
	public function options()
	{
		$field = $this->fieldFactory('list');
		$field->setLabel('Options');
		$field->setHelp('Each option on a different line');
		$field->setType('text');
		$field->setDisableRichText(true);
		$field->setLength(0);
		$this->addField($field);
	}
	
	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);
		$field->setType('enum');
		$field->setLength(str_replace(PHP_EOL, ',', $meta->getOption('list')));
		return $field;
	}
}