<?php


namespace Ajde;

use Ajde\Object\Standard;
use Ajde\Db\Table;



abstract class Filter extends Standard
{		
	const FILTER_IS			= ' IS ';
	const FILTER_NOT		= ' IS NOT ';	
	const FILTER_EQUALS		= ' = ';
	const FILTER_EQUALSNOT	= ' != ';
	const FILTER_GREATER	= ' > ';
	const FILTER_GREATEROREQUAL	= ' >= ';
	const FILTER_LESS		= ' < ';
	const FILTER_LESSOREQUAL= ' <= ';
	const FILTER_LIKE		= ' LIKE ';
	const FILTER_NOTLIKE	= ' NOT LIKE ';
	const FILTER_IN			= ' IN ';

    const CONDITION_WHERE   = 'where';
    const CONDITION_HAVING  = 'having';
	
	abstract public function prepare(Table $table = null);
}