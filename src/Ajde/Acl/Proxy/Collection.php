<?php


namespace Ajde\Acl\Proxy;

use Ajde\Collection as AjdeCollection;
use Ajde\Acl\Proxy\Model;
use \Ajde;
use Ajde\Http\Response;



class Collection extends AjdeCollection
{
    public $ignoreAccessControl = false;
    public $autoRedirect = true;

    public function getAclParam()
    {
        return '';
    }

    /**
     * @param Ajde_Acl_Proxy_Model $model
     * @return bool
     */
    private function canSkipModelValidation(Model $model)
    {
        return $model->validateAccess('read', false, true);
    }

    /**
     * @param bool $clean
     * @return bool
     */
    private function validateModels($clean = true)
    {
        if ($this->ignoreAccessControl === true) {
            return true;
        }
        if ($this->canSkipModelValidation($this->current())) {
            return true;
        }
        $newItems = array();
        foreach ($this as $key => $item) {
            /* @var $item Ajde_Acl_Proxy_Model */
            if (!$item->validateAccess('read', false)) {
                if ($clean) {
                    // No. Instead, add validated item to newItems array.
                    // Unsetting an internal Iterator array fucks up the indexes
                    // unset($this->_items[$key]);
                } else {
                    if ($this->autoRedirect == true) {
                        $this->validationErrorRedirect();
                    }
                }
            } else {
                $newItems[] = $item;
            }
        }
        $this->_items = $newItems;
        $this->rewind();
    }

    private function validationErrorRedirect()
    {
        Ajde::app()->getRequest()->set('message', __('You may not have the required permission to view this resource'));
        Ajde::app()->getResponse()->dieOnCode(Response::RESPONSE_TYPE_UNAUTHORIZED);
    }

    public function load()
    {
        parent::load();
        if ($this->count()) {
            $aclTimer = Ajde::app()->addTimer("<i>ACL validation for collection</i>");
            $this->validateModels();
            Ajde::app()->endTimer($aclTimer);
        }
        return $this->_items;
    }
}