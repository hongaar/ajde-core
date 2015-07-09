<?php


namespace Ajde\Embed;

use Ajde\Embed;
use Ajde\Http\Curl;
use Exception as Exception;
use Ajde\Exception\Log;
use Ajde\Core\Exception as AjdeException;



class Vimeo extends Embed
{
	public function convertUrlToEmbed() {
		if (substr($this->_code, 0, 7) == 'http://' || substr($this->_code, 0, 8) == 'https://') {
			$vimid = $this->_getVimeoId();
			$this->_code = '<iframe id="player_' . $vimid . '" src="http://player.vimeo.com/video/' . $vimid . '?title=0&amp;byline=0&amp;portrait=0&amp;api=1&amp;player_id=player_' . $vimid . '" width="400" height="225" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}
	}
	
	public function getCode()
	{
		$this->convertUrlToEmbed();
		$this->_setHeight();
		$this->_setWidth();
		$this->_code = str_replace('portrait=0', 'portrait=0', $this->_code);
		return $this->_code;
	}
	
	private function _getVimeoId()
	{
		if (substr($this->_code, 0, 7) == 'http://' || substr($this->_code, 0, 8) == 'https://') {
			return str_replace('/','',parse_url( $this->_code, PHP_URL_PATH ));
		} else {
			$matches = array();
			preg_match('%video/([0-9]+?)[\/\?\"]%', $this->_code, $matches);
			return isset($matches[1]) ? $matches[1] : null;
		}
	}
	
	public function getThumbnail() {
		$vmid = $this->_getVimeoId();
		if ($vmid) {
            $response = Curl::get("http://vimeo.com/api/v2/video/$vmid.php");
            try {
			    $hash = unserialize($response);
            } catch (Exception $e) {
                Log::logException(new AjdeException("Could not parse result from Vimeo"));
                return null;
            }
			return $hash[0]['thumbnail_large'];  
		}
		return null;
	}
}