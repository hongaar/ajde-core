<?php


namespace Ajde\Crud\Cms\Meta\Type;

use Ajde\Crud\Cms\Meta\Type;
use MetaModel;
use Ajde\Model;
use Ajde\Filter\Where;
use Ajde\Filter;



class Media extends Type
{
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
        $field = $this->fieldFactory('usemediatype');
        $field->setLabel('Media type');
        $field->setType('fk');
        $field->setIsRequired(false);
        $field->setModelName('mediatype');
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
        Model::register('media');
        $field = $this->decorationFactory($meta);
        $field->setType('fk');
        $field->setModelName('media');
        if ($meta->getOption('usemediatype')) {
            $field->setAdvancedFilter(array(
                new Where('mediatype', Filter::FILTER_EQUALS, $meta->getOption('usemediatype'))
            ));
        }
        if ($meta->getOption('popup')) {
            $field->setListRoute('admin/media:view.crud');
            $field->setUsePopupSelector(true);
            $field->setUseImage(true);
            $field->addTableFileField('thumbnail', UPLOAD_DIR);
            $field->setThumbDim(600, 200);
        }
//		$field->setUseImage(true);
//		$field->addTableFileField('thumbnail', UPLOAD_DIR);
//		$field->setThumbDim(300, 20);
        return $field;
    }
}