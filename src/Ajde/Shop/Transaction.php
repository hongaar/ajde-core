<?php


namespace Ajde\Shop;

use Ajde\Model;
use Config;
use Ajde\Shop\Transaction\Provider;
use Ajde\Db\Function;
use Ajde\Event\Dispatcher;
use Ajde\View;
use Ajde\Shop\Cart;
use Ajde\Core\Exception;
use TransactionItemCollection;
use Ajde\Filter\Where;
use Ajde\Filter;
use TransactionItemModel;



abstract class Transaction extends Model
{
	protected $_shippingModel;
    protected $_itemModel;

    public function __construct() {
        parent::__construct();

        $this->setEncryptedFields(array(
            'ip', 'name', 'email', 'shipment_address', 'shipment_zipcode', 'shipment_city', 'shipment_region', 'shipment_country', 'shipment_description', 'shipment_trackingcode', 'shipment_secret'
        ));
    }
	
	// Payment
	
	public static function getProviders()
	{
		return self::_getProviders();
	}
	
	private static function _getProviders()
	{
		$return = array();
		$providers = Config::get('transactionProviders');	
		foreach($providers as $provider) {
			$return[$provider] = Provider::getProvider($provider);
		}
		return $return;
	}
	
	/**
	 *
	 * @return Ajde_Shop_Transaction_Provider
	 */
	public function getProvider()
	{
		return Provider::getProvider($this->payment_provider, $this);
	}
	
	// Update IP / Secret
	
	public function beforeInsert()
	{
		$this->secret = $this->generateSecret();
		$this->ip = $_SERVER["REMOTE_ADDR"];

        // Added
        $this->added = new Function("NOW()");

        // Event
        Dispatcher::trigger($this, 'onCreate');
	}
	
	public function generateSecret($length = 255)
	{
		return substr(sha1(mt_rand()), 0, $length);
	}
	
	// Shipping
	
	/**
	 *
	 * @return Ajde_Shop_Shipping
	 */
	public function getShipping()
	{
		return $this->_getShippingModel();
	}	
	
	private function _getShippingModel()
	{
		$shippingModelName = $this->_shippingModel;
		return new $shippingModelName($this);
	}
	
	// Helpers

    public function getOrderId()
    {
        return date('Y') . str_pad($this->id + 1000, 6, '0', STR_PAD_LEFT);
    }

    public function displayOrderId()
    {
        $secret = '<span data-secret="' . $this->getSecret() . '"></span>';
        return $secret . $this->getOrderId();
    }
	
	protected function _format($value)
	{
		return money_format('%!i', $value);
	}
	
	public function getTotal()
	{
		return $this->payment_amount;
	}
	
	public function getFormattedTotal()
	{
		return Config::get('currency') . '&nbsp;' . $this->_format($this->getTotal());
	}
	
	public function getOverviewHtml()
	{
		if ($this->hasLoaded()) {
			$view = new View(MODULE_DIR . 'shop/', 'transaction/view');
			$view->assign('source', 'id');
			$view->assign('transaction', $this);
			return $view->render();
		} else  {
			return 'Order not found';
		}		
	}

    public function setItemsFromCart(Cart $cart)
    {
        // We can only add items if transaction exists
        if (!$this->exists()) throw new Exception('Can only add items to transaction if it exists');

        // Clear current items
        $items = new TransactionItemCollection();
        $items->addFilter(new Where('transaction', Filter::FILTER_EQUALS, $this->getPK()));
        $items->deleteAll();

        // Add items
        /** @var Ajde_Shop_Cart_Item $item */
        foreach($cart->getItems() as $cartItem) {
            $item = new TransactionItemModel();
            $item->transaction = $this->getPK();
            $item->entity = $cartItem->entity;
            $item->entity_id = $cartItem->entity_id;
            $item->unitprice = $cartItem->unitprice;
            $item->qty = $cartItem->qty;
            $item->insert();
        }
    }

    /**
     *
     * @return Ajde_Collection
     */
    public function getItems()
    {
        $collectionClass = $this->_itemModel;
        $collection = new $collectionClass();
        $collection->addFilter(new Where('transaction', Filter::FILTER_EQUALS, $this->getPK()));
        return $collection;
    }

    public function paid()
    {
        Dispatcher::trigger($this, 'onPaid');

        $this->payment_status = 'completed';
        $this->save();
    }
	
}