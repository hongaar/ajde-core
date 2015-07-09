<?php
namespace Ajde\Object;

use Ajde\Core\Exception;
use Ajde\Model;
use Ajde\Object\Magic as AjdeObjectMagic;


abstract class Magic extends Base
{
    protected $_data = array();

    public final function __call($method, $arguments)
    {
        $prefix = strtolower(substr($method, 0, 3));
        $key = substr($method, 3);
        $key = strtolower(substr($key, 0, 1)) . substr($key, 1);
        switch ($prefix) {
            case "get":
                if ($this->has($key)) {
                    return $this->get($key);
                } else {
                    if (!method_exists($this, '__fallback')) {
                        throw new Exception("Property '$key' not set in class " . get_class($this) . " when calling get('$key')", 90007);
                    }
                }
                break;
            case "set":
                return $this->set($key, $arguments[0]);
                break;
            case "has":
                return $this->has($key);
                break;
        }
        if (method_exists($this, '__fallback')) {
            return call_user_func_array(array($this, '__fallback'), array($method, $arguments));
        }
        throw new Exception("Call to undefined method " . get_class($this) . "::$method()", 90006);
    }

    /**
     *
     * @param $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value)
    {
        $this->_set($key, $value);
    }

    protected function _set($key, $value)
    {
        $this->_data[$key] = $value;
    }


    public function remove($key)
    {
        unset($this->_data[$key]);
    }

    public function get($key)
    {
        if ($this->has($key)) {
            return $this->_get($key);
        } else {
            throw new Exception("Parameter '$key' not set in class " . get_class($this) . " when calling get('$key')", 90007);
        }
    }

    protected function _get($key)
    {
        return $this->_data[$key];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->_data);
    }

    public function isEmpty($key)
    {
        $value = $this->get($key);
        return $value instanceof Model ?
            !$value->hasLoaded() :
            empty($value);
    }

    public function hasEmpty($key)
    {
        return $this->has($key) && $this->isEmpty($key);
    }

    public function hasNotEmpty($key)
    {
        if ($this->has($key)) {
            return !$this->isEmpty($key);
        }
        return false;
    }

    public function reset()
    {
        $this->_data = array();
    }

    public final function values()
    {
        return $this->_data;
    }

    public final function valuesAsSingleDimensionArray()
    {
        $array = array();
        foreach ($this->_data as $k => $item) {
            if (is_string($item)) {
                $array[$k] = $item;
            } else if (is_array($item)) {
                $array[$k] = serialize($item);
            } else if ($item instanceof AjdeObjectMagic) {
                $array[$k] = serialize($item->valuesAsSingleDimensionArray());
            } else if (is_object($item)) {
                $array[$k] = serialize($item);
            }
        }
        return $array;
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     * @see http://www.paulferrett.com/2009/php-camel-case-functions/
     * @param string $str String in camel case format
     * @return string $str Translated into underscore format
     */
    public static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @see http://www.paulferrett.com/2009/php-camel-case-functions/
     * @param string $str String in underscore format
     * @param bool $capitalise_first_char If true, capitalise the first char in $str
     * @return string $str translated into camel caps
     */
    public static function toCamelCase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    public static function classnameToUppercase($classname)
    {
        return str_replace(' ', '_', ucwords(str_replace('_', ' ', $classname)));
    }
}