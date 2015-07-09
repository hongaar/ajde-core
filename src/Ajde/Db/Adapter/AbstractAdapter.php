<?php


namespace Ajde\Db\Adapter;

use PDO as PDO;
use Ajde\Db\PDO as AjdeDbPDO;
use Exception as Exception;
use Ajde\Exception\Log;
use Ajde\Exception as AjdeException;



abstract class AbstractAdapter
{	
	public function __construct($dsn, $user, $password, $options)
	{
		$options = $options + array(
			// Not compatible with custom PDO::ATTR_STATEMENT_CLASS 
		    //PDO::ATTR_PERSISTENT 			=> true,					// Fast, please
		    PDO::ATTR_ERRMODE				=> PDO::ERRMODE_EXCEPTION 	// Exceptions, please);
		);
		try {
			$connection = new AjdeDbPDO($dsn, $user, $password, $options);
		} catch (Exception $e) {
			// Disable trace on this exception to prevent exposure of sensitive data
			// TODO: exception
			Log::logException($e);
			throw new AjdeException('Could not connect to database', 0, false);
		}
		$this->_connection = $connection;
	} 
	
	abstract public function getConnection();
	abstract public function getTableStructure($tableName);
	abstract public function getForeignKey($childTable, $childColumn);
	abstract public function getParents($childTable);
}