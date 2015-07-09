<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;



class Numeric extends Type
{
	public function getFields()
	{
		$this->required();
		$this->readonly();
		$this->length();
		$this->help();
		$this->defaultValue();
		return parent::getFields();
	}
	
	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);
		$field->setType('numeric');
		return $field;
	}
}