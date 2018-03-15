<?php

namespace Core\JFrame;

class autoload
{
    public static function run($class)
    {
        $classArr = explode('\\', $class);

        $className = array_pop($classArr);
        if(preg_match('/([a-z]{3})[A-Z]/', $className, $prefix)){
           $prefix = $prefix[1];
        }
        $dir = "";
        $module = isset($classArr[1]) ? $classArr[1] : "";
        if($prefix == 'cls'){
            $dir = JFRAME_LIBRARY_PATH . '/';
        }elseif($prefix == 'def'){
            $dir = JFRAME_DEF_PATH . '/';
        }else{
            switch(strtolower($classArr[0])){
                case "core":
                    $dir = JFRAME_CORE_PATH . '/';
                    break;
                case "library":
                    $dir = JFRAME_LIBRARY_PATH . '/';
                    break;
                case "module":
                    $dir = JFRAME_APP_PATH . '/';
                    break;
                default:
                    break;
            }
        }

        $dir .= $module ? $module . '/' : "";

        switch($prefix){
            case 'ctl':
                $dir .= "control/";break;
            case 'mdl':
                $dir .= "model/";break;
            case 'tpl':
                $dir .= "template/";break;
        }

        $file = $dir . $className . ".php";

        $file = str_replace('\\', '/', $file);
        if(is_file($file)){
            include($file);
        }
    }
}