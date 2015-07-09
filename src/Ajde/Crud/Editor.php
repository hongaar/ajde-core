<?php


namespace Ajde\Crud;

use Ajde\Object\Standard;



abstract class Editor extends Standard
{
	abstract function getResources(&$view);
}