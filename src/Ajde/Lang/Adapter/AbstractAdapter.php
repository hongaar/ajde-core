<?php


namespace Ajde\Lang\Adapter;

use Ajde\Component\String;



abstract class AbstractAdapter
{	
	public static function _($ident, $module = null)
	{
		return self::getInstance()->get($ident, $module);
	}
	
	public function getModule($module = null)
	{
		if (!$module) {	
			foreach(debug_backtrace() as $item) {			
				if (!empty($item['class'])) {
					if (is_subclass_of($item['class'], "Ajde_Controller")) {
                        $module = current(explode('_', String::toSnakeCase($item['class'])));
						break;
					}
				}
			}
		}		
		if (!$module) {
			$module = 'main';
		}
		return $module;
	}
	
	abstract public function get($ident, $module = null);
}