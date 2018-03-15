<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
date_default_timezone_set("Asia/Shanghai");

define("JFRAME_PATH", dirname(__DIR__));
define("JFRAME_CORE_PATH", JFRAME_PATH . '/core');
define("JFRAME_APP_PATH", JFRAME_PATH . '/app');
define("JFRAME_CONFIG_PATH", JFRAME_PATH . '/config');
define("JFRAME_LIBRARY_PATH", JFRAME_PATH . '/library');
define("JFRAME_RESOURCE_PATH", JFRAME_PATH . '/public');
define("JFRAME_DEF_PATH", JFRAME_PATH . '/def');

define('FRAME_ENV_IS_CLI', PHP_SAPI == 'cli');

require_once JFRAME_CORE_PATH . '/bootstrap.php';

