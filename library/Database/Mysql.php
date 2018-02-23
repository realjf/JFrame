<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/5/29
 * Time: 11:45
 */

namespace Library\Database;


class Mysql extends Database {

    protected  $_name = '';
    protected  $_primary = '';
    /**
     * 数据库连接句柄
     * @var
     */
    protected static $_handle = "";
    /**
     * 管理员表
     * @var string
     */
    protected static $_table = 'Admin';
    /**
     * pdo连接句柄
     * @var string
     */
    protected static $_dsn = '';



    /**
     * 获取数据库连接句柄
     */
    public static function getHandle() {
        $config = new \Yaf\Config\Ini(APPLICATION_PATH.'/conf/application.ini', SYS_ENV_IS_ONLINE ? 'product' : 'development');
        self::$_dsn = "mysql:host=".$config->database->get("params")->hostname.";dbname="
            .$config->database->get("params")->database.";charset=utf8";

        return self::$_handle = parent::getInstance(
            self::$_dsn,
            $config->database->get("params")->username,
            $config->database->get("params")->password
        );
    }
}