<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;



class Grouper extends Type
{
	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);
		$field->setType('header');
		return $field;
	}
}