<?php


namespace Ajde\Mailer\CssInliner;

use Ajde\Mailer\CssInliner\CssInlinerInterface;
use Ajde\Http\Curl;



class Torchbox implements CssInlinerInterface
{
    /**
     * @param string $html
     * @return mixed
     */
    public static function inlineCss($html)
    {
        $url = 'https://inlinestyler.torchbox.com:443/styler/convert/';
        $data = array(
            'returnraw' => '1',
            'source' => $html
        );
        return Curl::post($url, $data);
    }
}