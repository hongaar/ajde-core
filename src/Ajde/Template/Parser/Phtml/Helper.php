<?php 

namespace Ajde\Template\Parser\Phtml;

use Ajde\Object\Standard;
use Ajde\Template\Parser;
use \Ajde;
use Ajde\Component\Js;
use Ajde\Document\Format\Html;
use Ajde\Component\Css;
use Ajde\Component\Include;
use Ajde\Component\Form;
use Ajde\Component\Image;
use Ajde\Component\Qrcode;
use Ajde_Component_Markdown;
use Ajde\Component\Embed;
use Ajde\Component\Crud;
use Ajde\Component\String;




class Helper extends Standard
{
	/**
	 * 
	 * @var Ajde_Template_Parser
	 */
	protected $_parser = null;
	
	/**
	 * 
	 * @param Ajde_Template_Parser $parser
	 */
	public function __construct(Parser $parser)
	{
		$this->_parser = $parser;
	}
	
	/**
	 * 
	 * @return Ajde_Template_Parser
	 */
	public function getParser()
	{
		return $this->_parser;
	}
	
	/**
	 * 
	 * @return Ajde_Document 
	 */
	public function getDocument()
	{
		if ($this->getParser()->getTemplate()->has('document')) {
			return $this->getParser()->getTemplate()->getDocument();
		} else {
			return Ajde::app()->getDocument();
		}
	}
	
	/************************
	 * Ajde_Component_Js
	 ************************/
	
	/**
	 *
	 * @param string $name
	 * @param string $version
	 * @return void 
	 */
	public function requireJsLibrary($name, $version = false)
	{
		return Js::processStatic($this->getParser(), array('library' => $name, 'version' => $version));
	}
	
	/**
	 * 
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @param integer $position
	 * @return void
	 */
	public function requireJs($action, $format = 'html', $base = null, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		return Js::processStatic($this->getParser(), array('action' => $action, 'format' => $format, 'base' => $base, 'position' => $position, 'arguments' => $arguments));
	}
	
	/**
	 * 
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @return void
	 */
	public function requireJsFirst($action, $format = 'html', $base = null, $arguments = '')
	{
		return $this->requireJs($action, $format, $base, Html::RESOURCE_POSITION_FIRST, $arguments);
	}
	
	/**
	 *
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @return void
	 */
	public function requireJsTop($action, $format = 'html', $base = null, $arguments = '')
	{
		return $this->requireJs($action, $format, $base, Html::RESOURCE_POSITION_TOP, $arguments);
	}
	
	/**
	 * 
	 * @param string $filename
	 * @param integer $position
	 * @return void
	 */
	public function requireJsPublic($filename, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		return Js::processStatic($this->getParser(), array('filename' => $filename, 'position' => $position, 'arguments' => $arguments));
	}
	
	/**
	 * 
	 * @param string $url
	 * @param integer $position
	 * @return void
	 */
	public function requireJsRemote($url, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		return Js::processStatic($this->getParser(), array('url' => $url, 'position' => $position, 'arguments' => $arguments));
	}
	
	/************************
	 * Ajde_Component_Css
	 ************************/
	
	/**
	 *
	 * @param string $name
	 * @param string $version
	 * @return void 
	 */
	public function requireGWebFont($family, $weight = array(400), $subset = array('latin'))
	{
		return Css::processStatic($this->getParser(), array('fontFamily' => $family, 'fontWeight' => $weight, 'fontSubset' => $subset));
	}
	
	/**
	 * 
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @param integer $position
	 * @return void
	 */
	public function requireCss($action, $format = 'html', $base = null, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		return Css::processStatic($this->getParser(), array('action' => $action, 'format' => $format, 'base' => $base, 'position' => $position, 'arguments' => $arguments));
	}

	/**
	 * 
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @return void
	 */
	public function requireCssFirst($action, $format = 'html', $base = null, $arguments = '')
	{
		return $this->requireCss($action, $format, $base, Html::RESOURCE_POSITION_FIRST, $arguments);
	}
	
	/**
	 * 
	 * @param string $action
	 * @param string $format
	 * @param string $base
	 * @return void
	 */
	public function requireCssTop($action, $format = 'html', $base = null, $arguments = '')
	{
		return $this->requireCss($action, $format, $base, Html::RESOURCE_POSITION_TOP, $arguments);
	}
	
	/**
	 * 
	 * @param string $filename
	 * @param integer $position
	 * @return void
	 */
	public function requireCssPublic($filename, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
	{
		return Css::processStatic($this->getParser(), array('filename' => $filename, 'position' => $position, 'arguments' => $arguments));
	}

    /**
     *
     * @param string $url
     * @param integer $position
     * @return void
     */
    public function requireCssRemote($url, $position = Html::RESOURCE_POSITION_DEFAULT, $arguments = '')
    {
        return Css::processStatic($this->getParser(), array('url' => $url, 'position' => $position, 'arguments' => $arguments));
    }
	
	/************************
	 * Ajde_Component_Include
	 ************************/

	/**
	 *
	 * @param string $route
	 * @return string
	 */
	public function includeModule($route, $vars = array())
	{
		return Include::processStatic($this->getParser(), array('route' => $route, 'vars' => $vars));
	}
	
	/************************
	 * Ajde_Component_Form
	 ************************/

	/**
	 *
	 * @param string $route
	 * @param mixed $id
	 * @return string
	 */
	public function ACForm($route, $id = null, $class = null)
	{
		return Form::processStatic($this->getParser(), array('route' => $route, 'id' => $id, 'class' => $class));
	}
	
	/**
	 *
	 * @param string $route
	 * @param mixed $id
	 * @return string
	 */
	public function ACAjaxForm($route, $id = null, $class = null, $format = 'json')
	{
		return Form::processStatic($this->getParser(), array('route' => $route, 'ajax' => true, 'id' => $id, 'class' => $class, 'format' => $format));
	}
	
	/**
	 *
	 * @param string $target
	 * @return string
	 */
	public function ACAjaxUpload($name, $options = array(), $id = null, $class = null)
	{
		return Form::processStatic($this->getParser(), array('name' => $name, 'upload' => true, 'options' => $options, 'id' => $id, 'class' => $class));
	}

    /**
     *
     * @param string $route
     * @param mixed $id
     * @return string
     */
    public function ACEmbedForm($formId)
    {
        return Form::processStatic($this->getParser(), array('embed' => true, 'id' => $formId));
    }
	
	/************************
	 * Ajde_Component_Image
	 ************************/
	
	/**
	 *
	 * @param string $target
	 * @return string
	 */
	public function ACImage($attributes)
	{
		return Image::processStatic($this->getParser(), $attributes);
	}

    /**
     *
     * @param string $target
     * @return string
     */
    public function ACLazyImage($attributes)
    {
        return Image::processStatic($this->getParser(), array_merge($attributes, array('lazy' => true)));
    }
	
	/************************
	 * Ajde_Component_Qrcode
	 ************************/
	
	/**
	 *
	 * @param string $target
	 * @return string
	 */
	public function ACQrcode($attributes)
	{
		return Qrcode::processStatic($this->getParser(), $attributes);
	}
    
    /************************
	 * Ajde_Component_Markdown
	 ************************/
	
	/**
	 *
	 * @param string $target
	 * @return string
	 */
	public function ACMarkdown($attributes)
	{
		return Ajde_Component_Markdown::processStatic($this->getParser(), $attributes);
	}
    
    /************************
	 * Ajde_Component_Embed
	 ************************/
	
	/**
	 *
	 * @param string $attributes
	 * @return string
	 */
	public function ACEmbed($attributes)
	{
		return Embed::processStatic($this->getParser(), $attributes);
	}
	
	/************************
	 * Ajde_Component_Crud
	 ************************/

    /**
     *
     * @param mixed $model
     * @param array|Ajde_Crud_Options $options
     * @return Ajde_Crud
     */
	public function ACCrudList($model, $options = array())
	{
		return Crud::processStatic($this->getParser(),
			array(
				'list' => true,
				'model' => $model,
				'options' => $options
			)
		);
	}
	
	/**
	 *
	 * @param mixed $model
	 * @return Ajde_Crud
	 */
	public function ACCrudEdit($model, $id, $options = array())
	{
		return Crud::processStatic($this->getParser(),
			array(
				'edit' => true,
				'model' => $model,
				'id' => $id,
				'options' => $options
			)
		);
	}
	
	/**
	 * 
	 * @param Ajde_Crud $crud
	 * @return string
	 */
	public function ACCrudMainFilterBadge($crud, $refresh = false)
	{
		return Crud::processStatic($this->getParser(),
			array(
				'mainfilter' => true,
				'crud' => $crud,
				'refresh' => $refresh
			)
		);
	}
	
	/************************
	 * Ajde_Component_String
	 ************************/

	/**
	 *
	 * @param mixed $model
	 * @return string
	 */
	public function ACString($var)
	{
		return String::processStatic($this->getParser(),
			array(
				'escape' => true,
				'var' => $var
			)
		);
	}
	
	public function escape($var)
	{
		return $this->ACString($var);
	}
	
	public function clean($var)
	{
		return String::processStatic($this->getParser(),
			array(
				'clean' => true,
				'var' => $var
			)
		);
	}
}