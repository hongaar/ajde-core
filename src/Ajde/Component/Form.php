<?php 

namespace Ajde\Component;

use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Component\Form as AjdeComponentForm;
use stdClass;
use Ajde\Controller;
use Ajde\Core\Route;
use Ajde\Component\Exception;




class Form extends Component
{
	public static function processStatic(Parser $parser, $attributes)
	{
		$instance = new AjdeComponentForm($parser, $attributes);
		$t = new stdClass(); // Force unique object hash, see http://www.php.net/manual/es/function.spl-object-hash.php#76220
		return $instance->process();
	}
	
	protected function _init()
	{
		return array(
			'ajax' => 'ajax',
			'route' => 'form',
			'upload' => 'upload',
            'embed' => 'embed'
		);
	}
	
	public function process()
	{
		switch($this->_attributeParse()) {
		case 'form':
			$controller = Controller::fromRoute(new Route('_core/component:form'));
			
			$controller->setFormAction($this->attributes['route']);
			$controller->setFormId(issetor($this->attributes['id'], spl_object_hash($this)));
			$controller->setExtraClass(issetor($this->attributes['class'], ''));
			$controller->setInnerXml($this->innerXml);
			
			return $controller->invoke();
			break;
		case 'ajax':
			$controller = Controller::fromRoute(new Route('_core/component:formAjax'));
			$formAction = new Route($this->attributes['route']);
			$formAction->setFormat(issetor($this->attributes['format'], 'json'));

			$controller->setFormAction($formAction->__toString());
			$controller->setFormFormat(issetor($this->attributes['format'], 'json'));
			$controller->setFormId(issetor($this->attributes['id'], spl_object_hash($this)));
			$controller->setExtraClass(issetor($this->attributes['class'], ''));
			$controller->setInnerXml($this->innerXml);
			
			return $controller->invoke();
			break;
		case 'upload':
			$controller = Controller::fromRoute(new Route('_core/component:formUpload'));
			
			if (!isset($this->attributes['options']) ||
					!isset($this->attributes['options']['saveDir']) ||
					!isset($this->attributes['options']['extensions'])) {
				// TODO:
				throw new Exception('Options saveDir and extensions must be set for AC.Form.Upload');
			}
			
			$controller->setName($this->attributes['name']);
			$controller->setOptions($this->attributes['options']);
			$controller->setInputId(issetor($this->attributes['id'], spl_object_hash($this)));
			$controller->setExtraClass(issetor($this->attributes['class'], ''));
			
			return $controller->invoke();
			break;
        case 'embed':
            $controller = Controller::fromRoute(new Route('form/view.html'));
            $controller->setId($this->attributes['id']);

            return $controller->invoke();
            break;
		}
		// TODO:
		throw new Exception();	
	}
	
}