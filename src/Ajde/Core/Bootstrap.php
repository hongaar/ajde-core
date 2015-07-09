<?php

namespace Ajde\Core;

use Config;
use Closure;
use \Ajde;
use Ajde\Core\Exception;
use Ajde\Object\Base;


class Bootstrap
{
    public function run()
    {
        $cue = Config::getInstance()->bootstrap;
        $this->runCue($cue);
    }

    public function runCue($cue)
    {
        /*
         * Our bootstrapper calls the __bootstrap() methods on all modules defined
         * in Config::get("bootstrap").
         */
        $bootstrapFunction = '__bootstrap';

        foreach ($cue as $className) {
            if (is_object($className) && ($className instanceof Closure)) {
                $timer = Ajde::app()->addTimer('(closure)');
                $className->__invoke();
                Ajde::app()->endTimer($timer);
                return;
            }

            $timer = Ajde::app()->addTimer($className);

            // See if $className is a subclass of Ajde_Object
            if (!method_exists($className, "__getPattern")) {
                throw new Exception("Class $className has no pattern defined while it is configured for bootstrapping", 90001);
            }
            // Get bootstrap function callback
            $mode = call_user_func(array($className, "__getPattern"));
            if ($mode === Base::OBJECT_PATTERN_STANDARD) {
                $instance = new $className;
                $function = array($instance, $bootstrapFunction);
            } elseif ($mode === Base::OBJECT_PATTERN_SINGLETON) {
                $instance = call_user_func("$className::getInstance");
                $function = array($instance, $bootstrapFunction);
            } elseif ($mode === Base::OBJECT_PATTERN_STATIC) {
                $function = "$className::$bootstrapFunction";
            } elseif ($mode === null || $mode === Base::OBJECT_PATTERN_UNDEFINED) {
                throw new Exception("Class $className has no pattern defined while it is configured for bootstrapping", 90001);
            }
            // Execute bootstrap() function on $className
            if (!method_exists($className, $bootstrapFunction)) {
                throw new Exception("Bootstrap method in $className doesn't exist", 90002);
            } elseif (!call_user_func($function)) {
                throw new Exception("Bootstrap method in $className returned FALSE", 90003);
            }

            Ajde::app()->endTimer($timer);
        }
    }
}