<?php
/**
 * @source http://www.coderholic.com/php-database-query-logging-with-pdo/
 * Modified for use with Ajde_Document_Processor_Html_Debugger
 */

namespace Ajde\Db;

use PDO as PDO;
use Exception as Exception;
use Config;
use Ajde\Db\Exception as AjdeDbException;
use Ajde\Exception\Log;



 
/** 
* Extends PDO and logs all queries that are executed and how long 
* they take, including queries issued via prepared statements 
*/  
class PDO extends PDO
{  
    public static $log = array();  
  
    public function __construct($dsn, $username = null, $password = null, $options = array()) {
    	$options = $options + array(
    		PDO::ATTR_STATEMENT_CLASS => array('Ajde_Db_PDOStatement', array($this))
		);
        parent::__construct($dsn, $username, $password, $options);  
    }  
  
    public function query($query) {
    	//$cache = Ajde_Db_Cache::getInstance();
		$log = array('query' => $query);
		$start = microtime(true);
		//if (!$cache->has($query)) {
		
		try {
        	$result = parent::query($query);
		} catch (Exception $e) {
			if (Config::get('debug') === true) {
				if (isset($this->queryString)) dump($this->queryString);
				dump('Go to ' . Config::get('site_root') . '?install=1 to install DB');
				throw new AjdeDbException($e->getMessage());
			} else {
				Log::logException($e);
				die('DB connection problem. <a href="?install=1">Install database?</a>');
			}
		}		
			
		//$cache->set($query, serialize($result));
		//	$log['cache'] = false;			
		//} else {
		//	$result = $cache->get($query);
		//	$log['cache'] = true;
		//}  
		$time = microtime(true) - $start;  
		$log['time'] = round($time * 1000, 0);
        self::$log[] = $log;
        return $result;  
    }  
  
    public static function getLog() {  
        return self::$log;  
    }
}  
  
