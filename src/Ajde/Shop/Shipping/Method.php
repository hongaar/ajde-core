<?php


namespace Ajde\Shop\Shipping;

use Ajde\Object\Standard;
use Ajde\Shop\Transaction;
use Config;



abstract class Method extends Standard
{
	public function setTransaction(Transaction $transaction)
	{
		parent::setTransaction($transaction);
	}

	/**
	 *
	 * @return Ajde_Shop_Transaction
	 */
	protected function getTransaction()
	{
		return parent::getTransaction();
	}

	abstract public function getDescription();
	abstract public function getTotal();
	
	protected function _format($value)
	{
		return money_format('%!i', $value);
	}
	
	public function getFormattedTotal()
	{
		return Config::get('currency') . ' ' . $this->_format($this->getTotal());
	}
}