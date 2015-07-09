<?php


namespace Ajde\Shop\Transaction\Provider;

use Ajde\Shop\Transaction\Provider;
use Config;
use Ajde\Lang;
use Ajde\Component\String;
use Ajde\Model;
use TransactionModel;
use Ajde\Log;



class Paypal extends Provider
{
    protected function getMethod()
    {
        return '';
    }

    public function getName() {
        return 'PayPal';
    }

    public function getLogo() {
        return MEDIA_DIR . '_core/shop/paypal.png';
    }

    public function usePostProxy() {
        return true;
    }

    public function getRedirectUrl($description = null)
    {
        $url = $this->isSandbox() ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        return $this->ping($url) ? $url : false;
    }

    public function getRedirectParams($description = null) {
        $transaction = $this->getTransaction();

        // NOOOO.. THE UGLY HACKING
        $return = Config::get('site_root') . 'presale/transaction:complete';
        $method = $transaction->shipment_method;
        if ($method == 'presale-remainder') {
            $return = Config::get('site_root') . 'presale/transaction:confirm_complete';
        }

        return array(
            'cmd'			=> '_xclick',
            'business'		=> Config::get('shopPaypalAccount'),
            'notify_url'	=> Config::get('site_root') . $this->returnRoute . 'paypal' . $this->getMethod() . '.html',
            'bn'			=> Config::get('ident') . '_BuyNow_WPS_' . strtoupper(Lang::getInstance()->getShortLang()),
            'amount'		=> $transaction->payment_amount,
            'item_name'		=> issetor($description, Config::get('sitename') . ': ' . String::makePlural($transaction->shipment_itemsqty, 'item')),
            'quantity'		=> 1,
            'address_ override' => 1,
            'address1'		=> $transaction->shipment_address,
            'zip'			=> $transaction->shipment_zipcode,
            'city'			=> $transaction->shipment_city,
            'state'			=> $transaction->shipment_region,
            'country'		=> $transaction->shipment_country,
            'email'			=> $transaction->email,
            'first_name'	=> $transaction->name,
            'currency_code'	=> Config::get('currencyCode'),
            'custom'		=> $transaction->secret,
            'no_shipping'	=> 1, // do not prompt for an address
            'no_note'		=> 1, // hide the text box and the prompt
            'return'		=> $return,
            'rm'			=> 1 // the buyer’s browser is redirected to the return URL by using the GET method, but no payment variables are included
        );
    }

    public function updatePayment()
    {
        // PHP 4.1

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $header = '';
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen ($this->isSandbox() ? 'ssl://www.sandbox.paypal.com' : 'ssl://www.paypal.com', 443, $errno, $errstr, 30);

        // assign posted variables to local variables
        $item_name = issetor($_POST['item_name']);
        $item_number = issetor($_POST['item_number']);
        $payment_status = issetor($_POST['payment_status']);
        $payment_amount = issetor($_POST['mc_gross']);
        $payment_currency = issetor($_POST['mc_currency']);
        $txn_id = issetor($_POST['txn_id']);
        $receiver_email = issetor($_POST['receiver_email']);
        $payer_email = issetor($_POST['payer_email']);

        Model::register('shop');
        $secret = issetor($_POST['custom']);

        $transaction = new TransactionModel();
        $changed = false;

        if (!$fp) {
            // HTTP ERROR
        } else {
            fputs ($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets ($fp, 1024);
                if (strcmp ($res, "VERIFIED") == 0) {

                    if (!$transaction->loadByField('secret', $secret)) {
                        Log::log('Could not find transaction for PayPal payment with txn id ' . $txn_id . ' and transaction secret ' . $secret);

                        return array(
                            'success' => false,
                            'transaction' => null
                        );
                    }

                    // check the payment_status is Completed
                    // accept Pending from PayPal (eChecks?)
                    $acceptPending = true;

                    if ($payment_status == 'Completed' || ($acceptPending && $payment_status == 'Pending')) {
                        $details =	'AMOUNT: '			. $payment_amount		. PHP_EOL .
                            'CURRENCY: '		. $payment_currency		. PHP_EOL .
                            'PAYER_EMAIL: '		. $payer_email			. PHP_EOL .
                            'RECEIVER_EMAIL: '	. $receiver_email		. PHP_EOL .
                            'TXN_ID: '			. $txn_id				. PHP_EOL;
                        // update transaction only once
                        if ($transaction->payment_status != 'completed') {
                            $transaction->payment_details = $details;
                            $transaction->payment_status = 'completed';
                            $transaction->save();
                            $changed = true;
                        }

                        // Write pending to Log
                        if ($payment_status == 'Pending') {
                            Log::log('Status is Pending but accepting now. PayPal payment with txn id ' . $txn_id . ' and transaction secret ' . $secret);
                        }

                        return array(
                            'success' => true,
                            'changed' => $changed,
                            'transaction' => $transaction
                        );
                    } else {
                        if ($transaction->payment_status != 'refused') {
                            $transaction->payment_status = 'refused';
                            $transaction->save();
                            $changed = true;
                        }
                        Log::log('Status is not Completed but ' . $payment_status . ' for PayPal payment with txn id ' . $txn_id . ' and transaction secret ' . $secret);
                    }
                    // check that txn_id has not been previously processed
                    // check that receiver_email is your Primary PayPal email
                    // check that payment_amount/payment_currency are correct
                    // process payment
                } else if (strcmp ($res, "INVALID") == 0) {

                    if (!$transaction->loadByField('secret', $secret)) {
                        // secret not found anyway
                        $transaction = null;
                        Log::log('Could not find transaction for PayPal payment with txn id ' . $txn_id . ' and transaction secret ' . $secret);
                    } else {
                        // log for manual investigation
                        if ($transaction->payment_status != 'refused') {
                            $transaction->payment_status = 'refused';
                            $transaction->save();
                            $changed = true;
                        }
                        Log::log('Validation failed for PayPal payment with txn id ' . $txn_id);

                    }
                }
            }
            fclose ($fp);
        }
        return array(
            'success' => false,
            'changed' => $changed,
            'transaction' => $transaction
        );
    }
}