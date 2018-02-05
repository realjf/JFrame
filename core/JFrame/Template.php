<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2017/11/21
 * Time: 15:24
 */

namespace Core\JFrame;


class Template
{
    protected $__options = [];
    protected $__loader;

    public function __construct()
    {

    }

    public function setOption($key, $val)
    {
        $this->__options[$key] = $val;
    }

    public function setOptions(array $data)
    {
        foreach ($data as $k => $v){
            $this->__options[$k] = $v;
        }
    }

    public function display($tpl, $data, $tplDir = '')
    {
        $tplDir = $tplDir ?:  JFRAME_APP_PATH . "/" . ucfirst($this->__options['module']) . '/template';
        $loader = new \Twig_Loader_Filesystem($tplDir);
        $this->__loader = new \Twig_Environment($loader, array('debug' => true));
        try{
            $this->__loader->display($tpl, $data);
        }catch (\Exception $e){
            var_dump($e->getMessage());
            exit();
        }
    }
}