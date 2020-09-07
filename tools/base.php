<?php
date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL &  ~E_NOTICE);

define("JFRAME_PATH", dirname(__DIR__));
define("JFRAME_CORE_PATH", JFRAME_PATH . '/core');
define("JFRAME_APP_PATH", JFRAME_PATH . '/app');
define("JFRAME_CONFIG_PATH", JFRAME_PATH . '/config');
define("JFRAME_LIBRARY_PATH", JFRAME_PATH . '/library');
define("JFRAME_RESOURCE_PATH", JFRAME_PATH . '/public');
define("JFRAME_DEF_PATH", JFRAME_PATH . '/def');
define("JFRAME_MODEL_PATH", JFRAME_PATH . '/model');

define('FRAME_ENV_IS_CLI', PHP_SAPI == 'cli');

require_once JFRAME_CORE_PATH . '/JFrame/autoload.php';
require_once JFRAME_PATH . '/vendor/autoload.php';
require_once JFRAME_PATH . '/core/JFrame/Output.php';

use Core\JFrame\autoload;
use Core\JFrame\JFrame;

// register autoload
spl_autoload_register([new autoload(), 'run']);

define("SYS_IS_ONLINE", \Core\JFrame\Config::instance()->read('app.env') == 'pro' ? 1 : 0);
define("SYS_ENV", \Core\JFrame\Config::instance()->read('app.env'));
define("SYS_SERVER_HOST_NAME", \Core\JFrame\Config::instance()->read('server.hostname'));

/**
 * 日志输出
 * @param $str
 */
function showLog($str)
{
    ob_flush();
    echo date("Y-m-d H:i:s") . "\t" . $str . "\n";
}

function __a()
{
    \Core\JFrame\Output::abort(func_get_args());
}

