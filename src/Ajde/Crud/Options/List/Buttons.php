<?php


namespace Ajde\Crud\Options\List;

use Ajde\Crud\Options;



class Buttons extends Options
{	
	/**
	 *
	 * @return Ajde_Crud_Options_List 
	 */
	public function up($obj = false) {
		return parent::up($this);
	}
	
	// =========================================================================
	// Select functions
	// =========================================================================
			
	// =========================================================================
	// Set functions
	// =========================================================================
	
	/**
	 * Show the delete button
	 * 
	 * @param boolean $show
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function setDelete($show) { return $this->_set('delete', $show); }
	
	/**
	 * Show the new button
	 * 
	 * @param boolean $show
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function setNew($show) { return $this->_set('new', $show); }
	
	/**
	 * Show the edit button
	 * 
	 * @param boolean $show
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function setEdit($show) { return $this->_set('edit', $show); }

    /**
     * Show the view button
     *
     * @param boolean $show
     * @return Ajde_Crud_Options_List_Buttons
     */
    public function setView($show) { return $this->_set('view', $show); }

    /**
     * Sets the view URL
     *
     * @param string $function
     * @return Ajde_Crud_Options_List_Buttons
     */
    public function setViewUrlFunction($function) { return $this->_set('viewUrlFunction', $function); }

	/**
	 * Shows checkboxes
	 * 
	 * @param boolean $show
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function setSelect($show) { return $this->_set('select', $show); }
	
	/**
	 * Adds a custom button for every item in the list
	 * 
	 * @param name $name Identifier of the button
	 * @param text $text Text to display
	 * @param string $class Optional classname to add
	 * @param boolean $persistent Don't fold button
	 * @param boolean $function Call model function defined in $text (return false to omit button)
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function addItemButton($name, $text, $class = null, $persistent = false, $function = false) { 
		$buttons = ($this->has('itemButtons') ? $this->get('itemButtons') : array());
		$buttons[$name] = array('text' => $text, 'class' => isset($class) ? $class : $name, 'persistent' => $persistent, 'function' => $function);
		return $this->_set('itemButtons', $buttons);
	}

    public function resetItemButtons() {
        return $this->_set('itemButtons', array());
    }
	
	/**
	 * Adds a custom button for every item in the list
	 * 
	 * @param name $name Identifier of the button
	 * @param text $text Text to display
	 * @param type $class Optional classname to add
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function addToolbarHtml($name, $html) { 
		$toolbarHtml = ($this->has('toolbarHtml') ? $this->get('toolbarHtml') : array());
		$toolbarHtml[$name] = $html;
		return $this->_set('toolbarHtml', $toolbarHtml);
	}

    /**
     * Do not group toolbar buttons
     *
     * @param boolean $value
     * @return Ajde_Crud_Options_List_Buttons
     */
    public function setDoNotGroup($value)
    {
        return $this->_set('doNotGroup', $value);
    }
	
	/**
	 * Adds a custom button to the list toolbar
	 * 
	 * @param string $name Identifier of the button
	 * @param string $text Text to display
	 * @param string $class Optional classname to add
	 * @return Ajde_Crud_Options_List_Buttons 
	 */
	public function addToolbarButton($name, $text, $class = null) { 
		$buttons = ($this->has('toolbarButtons') ? $this->get('toolbarButtons') : array());
		$buttons[$name] = array('text' => $text, 'class' => isset($class) ? $class : $name);
		return $this->_set('toolbarButtons', $buttons);
	}
}