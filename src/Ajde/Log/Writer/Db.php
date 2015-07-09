<?php


namespace Ajde\Log\Writer;

use Ajde\Log\Writer\AbstractWriter;
use Ajde\Log;
use Ajde\Model;
use LogModel;



class Db extends AbstractWriter
{
    public static function _($message, $channel = Log::CHANNEL_INFO, $level = Log::LEVEL_INFORMATIONAL, $description = '', $code = '', $trace = '')
    {
        // don't use db writer on db error
        if (substr_count($message, 'SQLSTATE')) {
            return false;
        }

        Model::register('admin');

		$log = new LogModel();
        $log->populate(array(
            'message' => $message,
            'channel' => $channel,
            'level' => $level,
            'description' => $description,
            'code' => $code,
            'trace' => $trace,
            'request' => self::getRequest(),
            'user_agent' => self::getUserAgent(),
            'referer' => self::getReferer(),
            'ip' => self::getIP()
        ));
        return $log->insert();
	}
}