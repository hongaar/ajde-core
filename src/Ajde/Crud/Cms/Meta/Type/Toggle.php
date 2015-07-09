<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;



class Toggle extends Type
{
	public function getFields()
	{
		$this->readonly();
		$this->help();
		$this->defaultValue();
		return parent::getFields();
	}
	
	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);
		$field->setType('boolean');
		return $field;
	}
}