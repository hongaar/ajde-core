<?php


namespace Ajde\Crud\Editor;

use Ajde\Crud\Editor;
use Ajde\Exception;
use Ajde\Document\Format\Html;



class Ckeditor extends Editor
{
	function getResources(&$view) {
		/* @var $view Ajde_Template_Parser_Phtml_Helper */
		
		// Installed?
		if (!file_exists(PUBLIC_DIR . 'js/core/ckeditor/lib/ckeditor.js')) {
			// @todo
			throw new Exception('CKEditor not installed, see <a href="https://github.com/hongaar/ajde/tree/master/public/js/core/ckeditor">README</a>');
		}
		
		// Library files
		$view->requireJsPublic('core/ckeditor/lib/ckeditor.js');
		$view->requireJsPublic('core/ckeditor/lib/adapters/jquery.js');
		
		// Controller
		$view->requireJs('crud/field/text/ckeditor', 'html', MODULE_DIR . '_core/', Html::RESOURCE_POSITION_LAST);
	}
}