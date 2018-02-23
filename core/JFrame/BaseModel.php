<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/7
 * Time: 10:13
 */

namespace Core\JFrame;


use Library\Plugins\pluginsLoadData;

class BaseModel extends BaseClass
{
    protected $__name = "admin";

    const CONFIG_ADMIN_KEY = 'admin';
    const CONFIG_ONLINE_KEY = 'online';

    public function __construct()
    {

    }

    /**
     * @param null $name
     * @return \clsDatabase
     */
    public function database($name = NULL)
    {
        return pluginsLoadData::database($name = $name ?: $this->__name);
    }

    /**
     * @param null $name
     * @return bool|\clsRedis
     */
    public function redis($name = NULL)
    {
        return pluginsLoadData::redis($name = $name ?: $this->__name);
    }

    /**
     * @return \clsDatabase
     */
    public function dbAdmin()
    {
        return pluginsLoadData::database('admin');
    }
}