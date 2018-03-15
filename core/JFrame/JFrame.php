<?php

namespace Core\JFrame;

use Library\Route\libRoute;

class JFrame
{

    public function run()
    {
        // 加载路由表
        $libRoute = new libRoute();

        $router = $libRoute->dispatcher();

        $class = $this->_getClass($router);
        if(class_exists($class)){
            $controller = new $class();
            $method = "func" . ucfirst($router['method']);
            if(method_exists($class, $method)){
                $controller->setOptions($router);
                $controller->$method();
            }else{
                // 方法不存在
                $router['uri'] = str_ireplace($router['method'], "index", $router['uri']);
                $router['method'] = 'index';
                \clsTools::redirect($router['uri']);
            }
        }else{
            // 类不存在

        }
    }

    /**
     * @param $router
     * @return Template
     */
    private function _getClass($router)
    {
        $control = "ctl" . ucfirst($router['control']);
        return "Module\\" . ucfirst($router['module']) . "\\" . $control;
    }
}