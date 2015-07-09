<?php


namespace Ajde\Document\Processor\Css;

use Ajde\Object\Static;
use Ajde\Document\Processor;
use Ajde\Layout;
use Ajde\Resource\Local\Compressor;
use Ajde\Resource;
use lessc;
use Exception;
use Ajde\Exception\Log;


require_once 'lib/less/lessc.inc.php';

class Less extends Static implements Processor
{
	// Not implemented
	public static function preProcess(Layout $layout) {}
	public static function postCompress(Compressor $compressor) {}

	public static function preCompress(Compressor $compressor)
	{
		// Check type as this function can be called from Ajde_Event binding to
		// abstract Ajde_Resource_Local_Compressor class in Ajde_Resource_Local_Compressor::saveCache()
		if ($compressor->getType() == Resource::TYPE_STYLESHEET) {
			$compressor->setContents(self::lessifyCss($compressor->getContents()));
		}
	}

	public static function postProcess(Layout $layout)
	{
		$layout->setContents(self::lessifyCss($layout->getContents()));
	}

	public static function lessifyCss($css)
	{
		if (substr_count($css, '/*#!less*/') === 0) {
			return $css;
		}
		$less = new lessc();
		try {
			$lesser = $less->parse($css);
		} catch(Exception $e) {
			Log::logException($e);
			return $css;
		}
		return $lesser;
	}

}