<?php


namespace Ajde\Lang\Adapter;

use Ajde\Lang\Adapter\AbstractAdapter;
use Ajde\Lang;



class Ini extends AbstractAdapter
{
	// TODO: cache .ini files!
	
	public function get($ident, $module = null)
	{
		$module = $this->getModule($module);
		
		$lang = Lang::getInstance()->getLang();
		$iniFilename = LANG_DIR . $lang . DIRECTORY_SEPARATOR . $module . '.ini';
		if (is_file($iniFilename)) {
			$book = parse_ini_file($iniFilename);
			if (array_key_exists($ident, $book)) {
				return $book[$ident];
			}
		}
		return $ident;
	}
}