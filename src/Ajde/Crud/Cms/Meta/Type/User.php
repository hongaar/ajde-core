<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;
use Ajde\Model;



class User extends Type
{
	public function getFields()
	{
		$this->required();
		$this->readonly();
		$this->help();
		$this->defaultValue();
		return parent::getFields();
	}
	
	public function getMetaField(MetaModel $meta)
	{
		Model::register('user');
		$field = $this->decorationFactory($meta);
		$field->setType('fk');
		$field->setModelName('user');
		return $field;
	}
}