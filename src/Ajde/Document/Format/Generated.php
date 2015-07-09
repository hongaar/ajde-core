<?php


namespace Ajde\Document\Format;

use Ajde\Document\Format\Data;



abstract class Generated extends Data
{
    abstract public function generate($data);
}