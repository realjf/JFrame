<?php
/**
 * Created by PhpStorm.
 * User: Chen JieFeng
 * Date: 2016/11/30
 * Time: 14:58
 * FileName: pluginsRedis.php
 */

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
                $_dsn = sprintf("mysql:host=%s;dbname=%s;port=%s;",
                    $config['hostname'],
                    $config['database'],
                    $config['port']
                    );
                $options = [
                    'charset'   => $config['charset'] ?: 'utf8',
                ];
                $obj = new \clsDatabase($_dsn, $config['username'], $config['password'], $options);
            }else{
                return FALSE;
            }
            self::$_plugins[$type][$name] = & $obj;

        }
        return self::$_plugins[$type][$name];
    }
}