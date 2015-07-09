<?php


namespace Ajde\Crud\Editor;

use Ajde\Crud\Editor;
use Ajde\Document\Format\Html;



class Jwysiwyg extends Editor
{
	function getResources(Ajde_View &$view) {
		/* @var $view Ajde_Template_Parser_Phtml_Helper */
		
		// Library files
		$view->requireJsPublic('core/jwysiwyg/jwysiwyg.js');
		$view->requireCssPublic('core/jwysiwyg/jwysiwyg.css');
		
		// Controller
		$view->requireJs('crud/field/text/jwysiwyg', 'html', MODULE_DIR . '_core/', Html::RESOURCE_POSITION_LAST);
	}
}