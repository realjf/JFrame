<?php


namespace Core\JFrame;


class BaseClass
{
    static private $_modules = array();

    /**
     * @param string $val
     * @return mixed|$this
     */
    static public function instance($val = '')
    {
        $class = get_called_class();    // 获取调用该静态方法的类名（后期静态绑定类）
        $key = $val !== '' ? $class . '_' . $val : $class;
        if(!isset(self::$_modules[$key]) || !(self::$_modules[$key] instanceof $class)){
            self::$_modules[$key] = new $class($val);
        }
        return self::$_modules[$key];
    }

    public function __construct()
    {
    }

    public function __destruct()
    {

    }
}