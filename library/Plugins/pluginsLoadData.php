<?php

namespace Library\Plugins;


use Core\JFrame\Config;

class pluginsLoadData
{
    static private $_plugins = array();

    /**
     * @param $name
     * @return bool| \clsRedis
     */
    static public function redis($name)
    {
        return self::_loadPlugin($name, 'redis');
    }

    /**
     * @param $name
     * @return \clsDatabase
     */
    static public function database($name)
    {
        return self::_loadPlugin($name, 'database');
    }

    /**
     * @param $name
     * @param $type
     * @return mixed
     */
    static private function _loadPlugin($name, $type)
    {
        if(!isset(self::$_plugins[$type][$name])){
            $config = Config::instance()->read($type);
            if(!$config){
                return FALSE;
            }
            if($type == 'redis'){
                $_config = [
                    'host'  => $config['hostname'],
                    'port'  => $config['port'],
                    'timeout'  => $config['timeout'],
                ];
                $obj = new \clsRedis($_config);
            }elseif($type == 'database'){
                $obj = new \clsDatabase($config);
            }else{
                return FALSE;
            }
            self::$_plugins[$type][$name] = & $obj;

        }
        return self::$_plugins[$type][$name];
    }
}