<?php


namespace Ajde\Publisher;

use Ajde\Publisher;
use Ajde_Social_Provider_Twitter;
use Exception;
use Ajde\Log as AjdeLog;
use Ajde\Exception\Log as AjdeExceptionLog;



class Twitter extends Publisher
{
	private $_consumerKey;
	private $_consumerSecret;
	private $_token;
	private $_tokenSecret;
	
	private $_twitter;
	
	public function setOptions($options) {
		$this->_consumerKey = $options['consumerKey'];
		$this->_consumerSecret = $options['consumerSecret'];
		$this->_token = $options['token'];
		$this->_tokenSecret = $options['tokenSecret'];
		
		$this->_twitter =  new Ajde_Social_Provider_Twitter($this->_consumerKey, $this->_consumerSecret, $this->_token, $this->_tokenSecret);
	}
	
	public function publish()
	{
		$tweet = $this->getTitle();
		if ($url = $this->getUrl()) {
			$tweet = substr($tweet, 0, 140 - strlen($url) - 5) . '... ' .  $url;
		}
		
		while ($curlength = iconv_strlen(htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8'), 'UTF-8') >= 140) {
			$tweet = substr($tweet, 0, -1);
		}
		
		try {
			$response = $this->_twitter->post('statuses/update', array('status' => $tweet));
		} catch (Exception $e) {
			AjdeLog::log($response);
			AjdeExceptionLog::logException($e);
			return false;
		}
		
		if ($response->user && $response->user->id && $response->id_str) {
			return sprintf("http://twitter.com/%s/status/%s", $response->user->id, $response->id_str);
		} else {
			return false;
		}
	}
	
}