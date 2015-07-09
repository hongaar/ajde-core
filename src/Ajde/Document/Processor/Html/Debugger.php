<?php


namespace Ajde\Document\Processor\Html;

use Ajde\Object\Static;
use Ajde\Document\Processor;
use Ajde\Resource\Local\Compressor;
use Ajde\Layout;
use Ajde\Controller;
use Ajde\Core\Route;



class Debugger extends Static implements Processor
{			
	// Not implemented
	public static function preCompress(Compressor $compressor) {}
	public static function postCompress(Compressor $compressor) {}
	
	public static function preProcess(Layout $layout) {
		// invoke here to add resources
		Controller::fromRoute(new Route('_core/debugger:view.html'))->invoke();
	}
	
	public static function postProcess(Layout $layout) {
		$debugger = Controller::fromRoute(new Route('_core/debugger:view.html'))->invoke();
		$layout->setContents($layout->getContents() . $debugger);		
	}	
}