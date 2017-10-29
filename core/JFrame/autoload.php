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

        $dir .= $module ? $module . '/' : "";

        switch($prefix){
            case 'ctl':
                $dir .= "control/";break;
            case 'mdl':
                $dir .= "model/";break;
            case 'tpl':
                $dir .= "template/";break;
        }

        $file = $dir . ucfirst($className) . ".php";

        $file = str_replace('\\', '/', $file);
        echo $file . "<br/>";
        if(is_file($file)){
            include($file);
        }
    }
}