<?php


namespace Ajde\Crud\Export;

use Ajde\Object\Standard;
use Ajde\Crud\Export\ExportInterface;
use \Excel as Excel;



require_once 'excel.lib.php';

class Excel extends Standard implements ExportInterface
{
    /**
     * @var Excel
     */
    private $writer;

    public function prepare($title, $tableData)
    {
        $this->writer = new Excel($title);

        foreach ($tableData as $row) {
            $this->writer->home();
            foreach($row as $cell) {
                $this->writer->label($cell);
                $this->writer->right();
            }
            $this->writer->down();
        }
    }

    public function send()
    {
        $this->writer->send();
    }
}