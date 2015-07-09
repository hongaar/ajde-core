<?php


namespace Ajde\Resource;

use Ajde\Resource;



class Remote extends Resource
{

	public function  __construct($type, $url, $arguments = '')
	{
		$this->setUrl($url);
		$this->setArguments($arguments);
		parent::__construct($type);
	}

	public function getFilename()
	{
		return $this->getUrl();
	}

	protected function getLinkUrl()
	{
		return $this->getUrl();
	}

}