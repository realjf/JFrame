<?php

namespace Core\JFrame;

use Library\Route\libRoute;
use Noodlehaus\Config;

class JFrame
{
    private $_config;

    public function __construct()
    {
        $this->_config = new Config(JFRAME_CONFIG_PATH . '/config.ini');
    }

    public function run()
    {
        // 加载路由表
        $libRoute = new libRoute();

        $router = $libRoute->dispatcher();

        $control = "ctl" . ucfirst($router['control']);
        $class = "Module\\" . ucfirst($router['module']) . "\\" . $control;
        $controller = new $class();
        $method = "func" . ucfirst($router['method']);

        $controller->$method();
    }
}