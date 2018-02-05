<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/5
 * Time: 15:50
 */

namespace Core\JFrame;


class Output
{
    /**
     * @param array $arguments
     */
    public static function abort(array $arguments)
    {
        $sLine = FRAME_ENV_IS_CLI ? "\n--------------\n" : "<pre>\n";
        $eLine = FRAME_ENV_IS_CLI ? "\n--------------\n" : "</pre>";

        foreach($arguments as $a){
            echo $sLine;
            is_resource($a) ? var_dump($a) : var_export($a);
            echo $eLine;
        }
        exit();
    }
}

function __a()
{
    \Core\JFrame\Output::abort(func_get_args());
}