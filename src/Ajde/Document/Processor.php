<?php


namespace Ajde\Document;

use Ajde\Layout;
use Ajde\Resource\Local\Compressor;



interface Ajde_Document_Processor
{
	public static function preProcess(Layout $layout);	
	public static function postProcess(Layout $layout);
	
	public static function preCompress(Compressor $compressor);
	public static function postCompress(Compressor $compressor);
}