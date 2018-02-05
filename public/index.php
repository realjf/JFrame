<?php
//error_reporting(7);

define("JFRAME_PATH", dirname(__DIR__));
define("JFRAME_CORE_PATH", JFRAME_PATH . '/core');
define("JFRAME_APP_PATH", JFRAME_PATH . '/app');
define("JFRAME_CONFIG_PATH", JFRAME_PATH . '/config');
define("JFRAME_LIBRARY_PATH", JFRAME_PATH . '/library');
define("JFRAME_RESOURCE_PATH", JFRAME_PATH . '/public');

define('FRAME_ENV_IS_CLI', PHP_SAPI == 'cli');


require_once JFRAME_PATH . '/vendor/autoload.php';
require_once JFRAME_PATH . '/core/JFrame/Output.php';

// Whoops Exception Handler
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

require_once JFRAME_CORE_PATH . '/bootstrap.php';

