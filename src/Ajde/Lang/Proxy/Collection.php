<?php


namespace Ajde\Lang\Proxy;

use Ajde\Collection as AjdeCollection;



abstract class Collection extends AjdeCollection
{
	protected $languageField = 'lang';
	protected $languageRootField = 'lang_root';
	
	public function getLanguageField()
	{
		return $this->languageField;
	}
	
	public function getLanguageRootField()
	{
		return $this->languageRootField;
	}
}