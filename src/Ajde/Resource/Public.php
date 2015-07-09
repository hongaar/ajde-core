<?php


namespace Ajde\Resource;

use Ajde\Resource;



class Public extends Resource
{
	public function __construct($type, $filename, $arguments = '')
	{
		$url = 'public/' . $type . '/' . $filename;
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