<?php
require_once JFRAME_CORE_PATH . '/JFrame/autoload.php';

use Core\JFrame\autoload;
use Core\JFrame\JFrame;

// register autoload
spl_autoload_register([new autoload(), 'run']);

// bootstrap
$JFrame = new JFrame();

try{
    $JFrame->run();
}catch (Exception $e) {
    echo "system error!";
    exit();
}
