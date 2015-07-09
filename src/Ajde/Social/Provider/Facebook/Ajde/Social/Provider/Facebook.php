<?php


namespace Ajde\Social\Provider;

use \Facebook as Facebook;
use Config;



require_once "Facebook.lib.php";

class Facebook extends Facebook
{
    private $_key;
    private $_secret;

    public function __construct($config = array())
    {
        $this->_key = Config::get('ssoFacebookKey');
        $this->_secret = Config::get('ssoFacebookSecret');

        $config = array_merge($config, array(
            'appId'  => $this->_key,
            'secret' => $this->_secret,
        ));

        parent::__construct($config);
    }
}