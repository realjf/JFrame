<?php

define("JFRAME_PATH", dirname(__FILE__));
define("JFRAME_CORE_PATH", JFRAME_PATH . '/core');
define("JFRAME_APP_PATH", JFRAME_PATH . '/app');
define("JFRAME_CONFIG_PATH", JFRAME_PATH . '/config');
define("JFRAME_LIBRARY_PATH", JFRAME_PATH . '/library');

require_once 'vendor/autoload.php';

// Whoops Exception Handler
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

require_once JFRAME_CORE_PATH . '/bootstrap.php';

