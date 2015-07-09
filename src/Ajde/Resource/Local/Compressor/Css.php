<?php


namespace Ajde\Resource\Local\Compressor;

use Ajde\Resource\Local\Compressor;
use Ajde\Resource;



class Css extends Compressor
{
	private $_lib = 'cssmin'; // cssmin
	
	public function  __construct()
	{
		$this->setType(Resource::TYPE_STYLESHEET);
		parent::__construct();
	}
	
	public function compress()
	{		
		$compressor = $this->getCompressor($this->_contents);
		$compressed = $compressor->compress();
		
		$this->_contents = $compressed;
		return true;
	}
	
	public function getCompressor($contents)
	{
		require_once 'lib/' . ucfirst($this->_lib) . '.php';
		$className = 'Ajde_Resource_Local_Compressor_Css_' . ucfirst($this->_lib);
		return new $className($contents);		
	}
}