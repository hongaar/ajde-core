<?php


namespace Ajde\User;

use Ajde\User\Sso\SsoInterface;
use SsoModel;



abstract class Sso implements SsoInterface
{
    public function getUser()
    {
        $hash = $this->getUidHash();
        $model = new SsoModel();
        if ($hash && $model->loadByField('uid', $hash)) {
            $model->loadParent('user');
            return $model->getUser();
        } else {
            return false;
        }
    }
}