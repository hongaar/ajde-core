<?php


namespace Ajde\Document\Processor\Css;

use Ajde\Object\Static;
use Ajde\Document\Processor;
use Ajde\Layout;
use Ajde\Resource\Local\Compressor;
use Ajde\Resource;
use \CSS3Maximizer;


require_once 'lib/maximizer/CSS3Maximizer.php';
require_once 'lib/maximizer/inc/ColorSpace.php';

class Maximizer extends Static implements Processor
{
	// Not implemented
	public static function preProcess(Layout $layout) {}
	public static function postCompress(Compressor $compressor) {}

	public static function preCompress(Compressor $compressor)
	{
		// Check type as this function can be called from Ajde_Event binding to
		// abstract Ajde_Resource_Local_Compressor class in Ajde_Resource_Local_Compressor::saveCache()
		if ($compressor->getType() == Resource::TYPE_STYLESHEET) {
			$compressor->setContents(self::clean($compressor->getContents()));
		}
	}

	public static function postProcess(Layout $layout)
	{
		$layout->setContents(self::clean($layout->getContents()));
	}

	public static function clean($css)
	{
		$maximizer = new CSS3Maximizer();
		// try {
			$maximized = $maximizer->clean(array('css'=>$css));
		// } catch(Exception $e) {
		// 	Ajde_Exception_Log::logException($e);
		// 	return $css;
		// }
		return $maximized;
	}

}