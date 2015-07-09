<?php


namespace Ajde\Crud\Export;




interface Ajde_Crud_Export_Interface
{
    public function prepare($title, $tableData);
    public function send();
}