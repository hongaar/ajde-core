<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Crud as AjdeCrud;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Component\Exception;




class Crud extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new self($parser, $attributes);
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'list' => 'list',
			'edit' => 'edit',
			'mainfilter' => 'mainfilter'
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
		case 'list':
			$options = issetor($this->attributes['options'], array());
			$crud = new AjdeCrud($this->attributes['model'], $options);
			$crud->setAction('list');
			return $crud;				
			break;
		case 'edit':
			$options = issetor($this->attributes['options'], array());
			$id = issetor($this->attributes['id'], null);
			$crud = new AjdeCrud($this->attributes['model'], $options);
			$crud->setId($id);
			$crud->setAction('edit/layout');
			return $crud;
			break;
		case 'mainfilter':
			$controller = Controller::fromRoute(new Route('_core/crud:mainfilter'));
			$controller->setCrud($this->attributes['crud']);
			$controller->setRefresh(issetor($this->attributes['refresh'], false));
			return $controller->invoke();
		}		
		// TODO:
		throw new Exception();	
	}
}