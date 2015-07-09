<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;



class Products extends Type
{
	public function getFields()
	{
		$this->required();
		$this->readonly();
		$this->help();
		return parent::getFields();
	}


	public function getMetaField(MetaModel $meta)
	{
		$field = $this->decorationFactory($meta);

        $field->setType('multiple');
        $field->setModelName('product');
        $field->setCrossReferenceTable('node_meta_multiple');
        $field->addCrossReferenceTableConstraint('node_meta_multiple.meta', $meta->getPK());
        $field->setChildField('`foreign`');
        $field->setEditRoute('admin/shop:products.crud');
        $field->setListRoute('admin/shop:products.crud');
        $field->addTableFileField('image', MEDIA_DIR . ProductModel::$imageDir);
        $field->addTableField('unitprice');
        $field->setUsePopupSelector(true);
        $field->setThumbDim(100, 100);
        $field->addSortField('sort');

		return $field;
	}
}