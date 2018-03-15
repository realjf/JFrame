<?php
require_once JFRAME_CORE_PATH . '/JFrame/autoload.php';
require_once JFRAME_PATH . '/vendor/autoload.php';
require_once JFRAME_PATH . '/core/JFrame/Output.php';

use Core\JFrame\autoload;
use Core\JFrame\JFrame;

// register autoload
spl_autoload_register([new autoload(), 'run']);

// bootstrap
$JFrame = new JFrame();

define("SYS_IS_ONLINE", \Core\JFrame\Config::instance()->read('app.env') == 'pro' ? 1 : 0);
define("SYS_ENV", \Core\JFrame\Config::instance()->read('app.env'));

if(SYS_IS_ONLINE){
    try{
        $JFrame->run();
    }catch (Exception $e){
        echo("999:system error!");
        exit();
    }
}else{
    // Whoops Exception Handler
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
    $JFrame->run();
}

function __a()
{
    \Core\JFrame\Output::abort(func_get_args());
}
