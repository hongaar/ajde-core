<?php	

namespace Ajde\Template\Parser\Xhtml;

use DOMElement;
use Ajde\Component;
use Ajde\Template\Parser;
use Ajde\Core\Exception;




class Element extends DOMElement
{
	public function inACNameSpace()
	{
		return substr($this->nodeName, 0, 3) === Component::AC_XMLNS . ':';	
	}
	
	public function inAVNameSpace()
	{
		return substr($this->nodeName, 0, 3) === Component::AV_XMLNS . ':';	
	}
	
	public function processVariable(Parser $parser)
	{
		$variableName = str_replace(Component::AV_XMLNS . ':', '', $this->nodeName);
		if (!$parser->getTemplate()->hasAssigned($variableName)) {
			 throw new Exception("No variable with name '" . $variableName . "' assigned to template.", 90019);
		}
		$contents = (string) $parser->getTemplate()->getAssigned($variableName);
		/* @var $doc DOMDocument */
		$doc = $this->ownerDocument;
		$cdata = $doc->createCDATASection($contents);
		$this->appendChild($cdata);
	}
	
	public function processComponent(Parser $parser)
	{
		$component = Component::fromNode($parser, $this);
		$contents = $component->process();
		/* @var $doc DOMDocument */
		$doc = $this->ownerDocument;
		$cdata = $doc->createCDATASection($contents);
		$this->appendChild($cdata);
	}
	
}