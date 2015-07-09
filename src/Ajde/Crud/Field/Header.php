<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;



class Header extends Field
{
	public function getHtml()
	{
		$template = $this->_getInputTemplate();
		$template->assign('field', $this);
		return $template->render();
	}
	
	public function getInput($id = null)
	{
		return '';
	}
}