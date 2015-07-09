<?php


namespace Ajde\Mailer\CssInliner;

use Ajde\Mailer\CssInliner\CssInlinerInterface;



class Passthrough implements CssInlinerInterface
{
    /**
     * @param string $html
     * @return mixed
     */
    public static function inlineCss($html)
    {
        return $html;
    }
}