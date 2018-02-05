<?php

class clsVars
{
    /**
     * @param string $name
     * @return clsVarFilter
     */
    static public function get($name)
    {
        static $_instance = NULL;
        if(empty($_instance)){
            $_instance = new self($_GET);
        }
        return $_instance->read($name);
    }

    /**
     * @param string $name
     * @return clsVarFilter
     */
    static public function post($name)
    {
        static $_instance = NULL;
        if(empty($_instance)){
            $_instance = new self($_POST);
        }
        return $_instance->read($name);
    }

    /**
     * @param string $name
     * @return clsVarFilter
     */
    static public function cookie($name)
    {
        static $_instance = NULL;
        if(empty($_instance)){
            $_instance = new self($_COOKIE);
        }
        return $_instance->read($name);
    }

    /**
     * @param string $name
     * @return clsVarFilter
     */
    static public function request($name)
    {
        static $_instance = NULL;
        if(empty($_instance)){
            $_instance = new self($_REQUEST);
        }
        return $_instance->read($name);
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vGet($name, $default = NULL)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vPost($name, $default = NULL)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vCookie($name, $default = NULL)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vRequest($name, $default = NULL)
    {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vFile($name, $default = NULL)
    {
        return isset($_FILES[$name]) ? $_FILES[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vServer($name, $default = NULL)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    static public function vSession($name, $default = NULL)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    /**
     * @var array
     */
    private $_data = array();

    public function __construct($array = array())
    {
        $this->_data = (array)$array;
    }

    /**
     * @param string $name
     * @return clsVarFilter
     */
    public function read($name)
    {
        return isset($this->_data[$name]) ? new clsVarFilter($this->_data[$name]) : new clsVarFilter;
    }

    /**
     * @return $this
     */
    public function remove()
    {
        foreach(func_get_args() as $name){
            unset($this->_data[$name]);
        }
        return $this;
    }

    /**
     * 获取所有数据
     * @return array
     */
    public function all()
    {
        return $this->_data;
    }
}