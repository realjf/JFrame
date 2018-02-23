<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/5
 * Time: 17:04
 */

namespace Core\JFrame;


use Noodlehaus\Config as NConfig;

class Config extends BaseClass
{
    private static $_config = null;


    public function __construct()
    {
        if(self::$_config == null){
            self::$_config = NConfig::load(JFRAME_CONFIG_PATH . '/config.ini');
        }
    }

    /**
     * 读取配置，支持嵌套key
     * @param $key
     * @return mixed|null
     */
    public function read($key)
    {
        return self::$_config->get($key);
    }
}