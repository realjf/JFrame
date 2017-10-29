<?php
/**
 * Created by PhpStorm.
 * User: Chen Jiefeng
 * Date: 2017/10/29
 * Time: 17:14:05
 */

namespace Library\Route;

class libRoute
{
    private $_config;

    private $_router = [
        'control' => 'index',
        'method'  => 'index',
        'module'    => 'index',
    ];


    public function __construct()
    {
        $this->_config = new \Noodlehaus\Config(JFRAME_CONFIG_PATH . '/config.ini');
    }

    public function dispatcher()
    {
        $request = $this->getUri();
        switch($this->_config->get('route.model')){
            case 'path' : // /index/run/id/1 （需要开启服务器rewrite模块，并且配置.htaccess）
                return [];
            case 'rewrite':
                // /index/run/?id=1 （需要开启服务器rewrite模块，并且配置.htaccess）
                return [];
            case "html":    // user-index-run.htm?uid=100 （需要开启服务器rewrite模块，并且配置.htaccess）
                return $this->parseHtmlUri($request);
            default: // index.php?c=index&a=run
                return [];
        }
    }

    public function getUri()
    {
        $filter_param = array('<', '>', '"', "'", '%3C', '%3E', '%22', '%27', '%3c', '%3e');
        $uri = str_replace($filter_param, '', $_SERVER['REQUEST_URI']);
        if($pos = strpos($uri, '?')){
            $uri = substr($uri, 0, $pos);
        }
        return $uri;
    }

    public function parseHtmlUri($request)
    {
        if(!$request){
            return $this->_router;
        };
        $request = trim($request, '/');
        if(!preg_match('/([a-zA-Z]+)\/([a-zA-Z]+)\-([a-zA-Z]+)\.html/i', $request, $match)){
            return $this->_router;
        }
        $this->_router = [
            'module' => $match[1],
            'control' => $match[2],
            'method'  => $match[3],
        ];
        return $this->_router;
    }
}