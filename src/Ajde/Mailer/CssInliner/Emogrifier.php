<?php


namespace Ajde\Mailer\CssInliner;

use Ajde\Mailer\CssInliner\CssInlinerInterface;



class Emogrifier implements CssInlinerInterface
{
    /**
     * @param string $html
     * @return mixed
     */
    public static function inlineCss($html)
    {
        if (class_exists('\Pelago\Emogrifier')) {
            $emogrifier = new \Pelago\Emogrifier($html);
            return $emogrifier->emogrify();
        } else {
            return $html;
        }
    }
}