<?php


namespace Ajde\Exception;

use Ajde\Object\Static;
use Exception;
use Ajde\Exception\Handler;
use Ajde\Log as AjdeLog;



class Log extends Static
{
	static public function logException(Exception $exception)
	{
        $type = Handler::getTypeDescription($exception);
        $level = Handler::getExceptionLevelMap($exception);
        $channel = Handler::getExceptionChannelMap($exception);
		$trace = strip_tags( Handler::trace($exception, Handler::EXCEPTION_TRACE_ONLY) );

        AjdeLog::_($exception->getMessage(), $channel, $level, $type, sprintf("%s on line %s", $exception->getFile(), $exception->getLine()), $trace);
	}
}