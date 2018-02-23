<?php

/**
 * Created by PhpStorm.
 * User: Chen JieFeng
 * Date: 2016/11/30
 * Time: 18:33
 * FileName: clsDatabase.php
 */
class clsDatabase extends \Library\Database\Database
{
    public function __construct($dns, $user, $passwd, $options = array())
    {
        parent::__construct($dns, $user, $passwd, $options);
    }

    /**
     * 字符转义
     * @param mixed $var
     * @return float|int|string
     */
    static public function slashes($var)
    {
        if(is_null($var)){
            return 'null';
        } elseif(is_int($var)){
            return intval($var);
        } elseif(is_bool($var)){
            return $var ? 'true' : 'false';
        } elseif(is_float($var)){
            return floatval($var);
        }

        return addslashes($var);
    }
}