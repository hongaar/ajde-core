<?php


namespace Ajde\Resource;

use Ajde\Object\Static;



class GWebFont extends Static
{
	public static $base = '//fonts.googleapis.com/css?';
	
	public static function getUrl($family, $weight = array(400), $subset = array('latin'))
	{
		if (is_array($weight)) {
			$weight = implode(',', $weight);
		}
		if (is_array($subset)) {
			$subset = implode(',', $subset);
		}
		$qs = array(
			'family' => $family . ':' . $weight,
			'subset' => $subset			
		);
		return self::$base . http_build_query($qs);
	}
}