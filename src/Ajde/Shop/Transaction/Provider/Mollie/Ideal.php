<?php


namespace Ajde\Shop\Transaction\Provider\Mollie;

use Ajde\Shop\Transaction\Provider\Mollie;



class Ideal extends Mollie
{
    public function getName() {
        return 'iDeal';
    }

    public function getLogo() {
        return MEDIA_DIR . '_core/shop/ideal.png';
    }

    protected function getMethod()
    {
        return 'ideal';
    }
}