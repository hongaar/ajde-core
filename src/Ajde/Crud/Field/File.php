<?php


namespace Ajde\Crud\Field;

use Ajde\Crud\Field;
use Ajde\Component\String;
use Ajde\Exception;



class File extends Field
{
	protected function _getHtmlAttributes()
	{
		$attributes = array();
		$attributes['type'] = "hidden";
		$attributes['value'] = String::escape($this->getValue());
		return $attributes;		
	}
	public function getSaveDir()
	{
		if (!$this->hasSaveDir()) {
			// TODO:
			throw new Exception('saveDir not set for Ajde_Crud_Field_File');
		}
		return parent::getSaveDir();
	}
	
	public function getExtensions()
	{
		if (!$this->hasSaveDir()) {
			// TODO:
			throw new Exception('extensions not set for Ajde_Crud_Field_File');
		}
		return parent::getExtensions();
	}
	
	public function getOverwrite()
	{
		if (!$this->hasOverwrite()) {
			$this->setOverwrite(false);
		}
		return parent::getOverwrite();
	}
}