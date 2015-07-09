<?php


namespace Ajde\Crud\Editor;

use Ajde\Crud\Editor;
use Ajde\Document\Format\Html;



class Aloha extends Editor
{
	function getResources(Ajde_View &$view) {
		/* @var $view Ajde_Template_Parser_Phtml_Helper */
		
		// Controller
		$view->requireJs('crud/field/text/aloha', 'html', MODULE_DIR . '_core/', Html::RESOURCE_POSITION_LAST);
		
		// Library files
		$plugins = array(
			'common/format',
			'common/highlighteditables',
			'common/contenthandler',
			'common/list',
			'common/link',
			'common/table',
			'common/undo',
			'common/paste'
			/*'common/block'*/
		);
		$view->requireJsPublic('core/aloha/ajde.js', Html::RESOURCE_POSITION_LAST);
		$view->requireJsPublic('core/aloha/lib/aloha.js', Html::RESOURCE_POSITION_LAST, 'data-aloha-plugins="' . implode(',', $plugins) . '"');		
		$view->requireCssPublic('core/aloha/aloha.css');
				
	}
}