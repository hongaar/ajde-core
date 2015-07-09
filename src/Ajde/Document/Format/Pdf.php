<?php


namespace Ajde\Document\Format;

use Ajde\Document\Format\Generated;
use Config;
use Ajde\Http\Curl;



class Pdf extends Generated
{
    const METHOD_SNAPPY = 'snappy';
    const METHOD_WEB2PDF = 'web2pdf';

    public function generate($data)
    {
        $url = $data['url'];
        $filename = $data['filename'];

        $method = Config::get('pdfMethod');

        switch ($method) {
            case self::METHOD_SNAPPY:
                return $this->snappy($url);
                break;
            case self::METHOD_WEB2PDF:
                return $this->web2pdf($url, $filename);
                break;
        }
    }

    private function snappy($url)
    {
        $bin = (PHP_INT_SIZE === 8) ?
            'bin/wkhtmltopdf-amd64' :
            'bin/wkhtmltopdf-i386';

        $snappy = new \Knp\Snappy\Pdf(Config::get('local_root') . '/' . VENDOR_DIR . $bin);

        $snappy->setOption('print-media-type', true);
        $snappy->setOption('disable-javascript', true);
        $snappy->setOption('lowquality', false);
        $snappy->setOption('load-error-handling', 'ignore');

        // Use $snappy->getOptions() to see all possible options
        // @see http://wkhtmltopdf.org/usage/wkhtmltopdf.txt
        // d($snappy->getOptions());exit;

        return $snappy->getOutput($url);
    }

    private function web2pdf($url, $filename = null)
    {
        $web2pdfRoot = Config::get('pdfWeb2PdfApi');
        $api = $web2pdfRoot . '?url=' . urlencode($url) . '&filename=' . urlencode($filename);

        return Curl::get($api);
    }
}