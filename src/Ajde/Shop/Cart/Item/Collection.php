<?php


namespace Ajde\Shop\Cart\Item;

use Ajde\Collection as AjdeCollection;
use Ajde\Shop\Cart;
use Ajde\Filter\Where;
use Ajde\Filter;
use Config;



class Collection extends AjdeCollection
{	
	protected $_autoloadParents = false;
		
	public function __construct(Cart $cart = null) {
		parent::__construct();
		if (isset($cart)) {
			$this->setCart($cart);
			$this->addFilter(new Where('cart', Filter::FILTER_EQUALS, $cart->getPK()));
		}
	}
	
	protected function _format($value)
	{
		if (function_exists('money_format')) {
			return money_format('%!i', $value);
		} else {
			return $value;
		}
	}
	
	public function getTotal()
	{
		$total = 0;
		foreach($this as $item) {
			/* @var $item Ajde_Shop_Cart_Item */
			$total = $total + $item->getTotal();
		}
		return $total;
	}
	
	public function getFormattedTotal()
	{
		return Config::get('currency') . '&nbsp;' . $this->_format($this->getTotal());
	}
		
	public function getVATAmount()
	{
		$vat = 0;
		foreach($this as $item) {
			/* @var $item Ajde_Shop_Cart_Item */
			$vat = $vat + $item->getVATAmount();
		}
		return $vat;
	}
	
	public function getFormattedVATAmount()
	{
		return Config::get('currency') . '&nbsp;' . $this->_format($this->getVATAmount());
	}
	
	public function countQty()
	{		
		$qty = 0;
		foreach($this as $item) {
			/* @var $item Ajde_Shop_Cart_Item */
			$qty = $qty + $item->getQty();
		}
		return $qty;
	}
}