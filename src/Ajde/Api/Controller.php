<?php 

namespace Ajde\Api;

use Ajde\Acl\Controller as AjdeAclController;
use \Ajde;
use Ajde\Model;
use UserModel;
use Ajde\Http\Response;




abstract class Controller extends AjdeAclController
{		
	public function beforeInvoke($allowed = array()) {

		$token = Ajde::app()->getRequest()->getParam('token', false);
        if ($token) {
            Model::register('user');
            $user = new UserModel();
            list($uid, $hash) = explode(':', $token);

            if ($user->loadByPK($uid)) {
                if ($user->getCookieHash(false) === $hash) {
                    $user->login();
                }
            }
        }

        $user = UserModel::getLoggedIn();

        if ($user) {
            return parent::beforeInvoke($allowed);
        }
		
		Ajde::app()->getRequest()->set('message', __('You may not have the required permission to view this page'));
		Ajde::app()->getResponse()->dieOnCode(Response::RESPONSE_TYPE_UNAUTHORIZED);
	}
}