<?php 

namespace Ajde\Core\Exception;

use Ajde\Exception;




class Deprecated extends Exception
{
	public function __construct($message = null, $code = null)
	{
		$message = $message ? $message : 'Call to deprecated function or method';
		parent::__construct($message, $code);
	}
}