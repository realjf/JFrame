<?php


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