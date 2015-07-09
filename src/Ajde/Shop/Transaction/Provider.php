<?php


namespace Ajde\Shop\Transaction;

use Ajde\Object\Standard;
use Ajde_Shop_Transaction_Provider_Interface;
use Ajde\Core\ExternalLibs;
use Ajde\Core\Autoloader;
use Ajde\Core\Exception;
use Config;
use Ajde\Log;



interface Ajde_Shop_Transaction_Provider_Interface
{
	public function getName();	
	public function getLogo();
	public function usePostProxy();
	public function getRedirectUrl($description = null);
	public function getRedirectParams($description = null);
	public function updatePayment();
}

abstract class Provider extends Standard
implements Ajde_Shop_Transaction_Provider_Interface
{
    public $returnRoute = 'shop/transaction:callback/';

	/**
	 *
	 * @param string $name
	 * @param Ajde_Shop_Transaction $transaction
	 * @return Ajde_Shop_Transaction_Provider
	 * @throws Ajde_Exception 
	 */
	public static function getProvider($name, $transaction = null)
	{
		$providerClass = ExternalLibs::getClassname( 'Ajde_Shop_Transaction_Provider_' . self::classnameToUppercase($name) );
		if (!Autoloader::exists($providerClass)) {
			// TODO:
			throw new Exception('Payment provider ' . $name . ' not found');
		}
		$obj = new $providerClass();
		if ($transaction) {
			$obj->setTransaction($transaction);
		}
		return $obj;
	}
	
	public function setTransaction($transaction) 
	{
		parent::setTransaction($transaction);
	}
	
	/**
	 *
	 * @return Ajde_Shop_Transaction
	 */
	public function getTransaction()
	{
		return parent::getTransaction();
	}
	
	public function isSandbox()
	{
		return Config::get('shopSandboxPayment');
	}
	
	protected function ping($url, $port = 80, $timeout = 6)
	{
		$host = parse_url($url, PHP_URL_HOST);
		$fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$fsock) {
			Log::log('Ping for ' . $host . ':' . $port . ' (timeout=' . $timeout . ') failed');
			return false;
		} else {
			return true;
		}
	}
}