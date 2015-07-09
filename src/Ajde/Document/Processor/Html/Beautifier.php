<?php


namespace Ajde\Document\Processor\Html;

use Ajde\Object\Static;
use Ajde\Document\Processor;
use Ajde\Layout;
use Ajde\Resource\Local\Compressor;
use Ajde\Core\Autoloader;
use Ajde\Core\Exception;
use Tidy;



class Beautifier extends Static implements Processor
{
	// Not implemented
	public static function preProcess(Layout $layout) {}
	public static function preCompress(Compressor $compressor) {}
	public static function postCompress(Compressor $compressor) {}
		
	public static function postProcess(Layout $layout)
	{
		$layout->setContents(self::beautifyHtml($layout->getContents()));
	}

	public static function beautifyHtml($html,
		// @see http://tidy.sourceforge.net/docs/quickref.html
		$config = array(
			"output-xhtml" 	=> true,
			"char-encoding"	=> "utf8",
			"indent" 		=> true,
			"indent-spaces"	=> 4,
			"wrap"			=> 0
		)
	)
	{
		if (!Autoloader::exists('Tidy')) {
			throw new Exception('Class Tidy not found', 90023);
		}
		$tidy = new Tidy();
		// tidy does not produce valid utf8 when the encoding is specified in the config
		// so we provide a third parameter, 'utf8' to fix this
		// @see http://bugs.php.net/bug.php?id=35647
		return $tidy->repairString($html, $config, 'utf8');
	}
	
}