


namespace Ajde\Crud\Cms;
<?php



use Ajde\Crud\Cms\Meta\Fieldlist;
use Ajde\FileSystem\Find;
use Ajde\Core\Autoloader;
use Ajde\Core\Exception;
use Ajde\Model;
use MetaCollection;
use Ajde\Filter\WhereGroup;
use Ajde\Filter\Where;

class Meta extends Fieldlist
{
	private $_types;
	
	/**
	 * 
	 * @param int $id
	 * @return Ajde_Crud_Cms_Meta_Type
	 */
	public static function fromType($type)
	{
		$metaTypeClass = "Ajde_Crud_Cms_Meta_Type_" . ucfirst(str_replace(' ', '', $type));
		$metaType = new $metaTypeClass();
		return $metaType;				
	}
	
	public function __construct() {
		
	}

    /**
     * @return Ajde_Crud_Cms_Meta_Type[]
     */
	public function getTypes()
	{
		if (!$this->_types) {
			$ds = DIRECTORY_SEPARATOR;
			$files = Find::findFiles(LIB_DIR.'Ajde'.$ds.'Crud'.$ds.'Cms'.$ds.'Meta'.$ds.'Type'.$ds, '*.php');
			foreach($files as $file) {
				$filename = pathinfo($file, PATHINFO_FILENAME);
				$className = "Ajde_Crud_Cms_Meta_Type_" . ucfirst($filename);
				$this->_types[strtolower($filename)] = new $className();
			}
            ksort($this->_types);
		}
		return $this->_types;
	}

    /**
     * @return array
     */
    public function getTypesNiceNames()
    {
        $list = array();
        foreach($this->getTypes() as $type)
        {
            $list[] = $type->niceName();
        }
        return $list;
    }
	
	/**
	 * 
	 * @param string $name
	 * @return Ajde_Crud_Cms_Meta_Type
	 * @throws Ajde_Exception
	 */
	public function getType($name)
	{
		$className = "Ajde_Crud_Cms_Meta_Type_" . ucfirst(str_replace(' ', '', strtolower($name)));
		if (!Autoloader::exists($className)) {
			// TODO:
			throw new Exception('Meta field class ' . $className . ' could not be found'); 
		}
		return new $className();
	}
	
	public function getFields()
	{
		if (!$this->hasFields()) {
			// Reset all fields
			$this->setFields(array());

			// Iterate all available types
			foreach($this->getTypes() as $type) {
				/* @var $type Ajde_Crud_Cms_Meta_Type */

				// Iterate all fields of type
				foreach ($type->getFields() as $key => $field) {
					if ($this->hasField($key)) {
						$field = $this->getField($key);
					}
					$field->addShowOnlyWhen('type', $type->className());
					$this->setField($key, $field);
				}
			}
		}
		return parent::getFields();
	}
	
	public function getMetaFields($crossReferenceTable, $crossReferenceField, $sortField, $parentField, $filters = array())
	{
		$allFields = array();
		
		Model::register('admin');
		$metas = new MetaCollection();
		$metas->concatCrossReference($crossReferenceTable, $crossReferenceField);
		$metas->concatField($crossReferenceTable, $sortField);
		if (!empty($filters)) {
			$group = new WhereGroup();
			foreach($filters as $filter) {
				if ($filter instanceof Where) {
					$group->addFilter($filter);
				} else {
					$metas->addFilter($filter);
				}		
			}
			$metas->addFilter($group);
		}
		foreach($metas as $meta) {
			$metaField = $this->getType($meta->get('type'));
			$fieldOptions = $metaField->getMetaField($meta);
			// add show only when
			foreach(explode(',', $meta->get($crossReferenceField)) as $parentValue) {
				$fieldOptions->addShowOnlyWhen($parentField, $parentValue);
			}
			// add sorting
			foreach(explode(',', $meta->get($sortField)) as $parentValue) {
				$fieldOptions->addDynamicSort($parentField, $parentValue);
			}
			$allFields['meta_' . $meta->getPK()] = $fieldOptions;
		}
		return $allFields;
	}
}