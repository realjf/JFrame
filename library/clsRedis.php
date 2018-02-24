<?php


class clsRedis extends Redis
{
    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->connect($config['host'], intval($config['port']) ?: 6379, floatval($config['timeout']) ?: 1);
        if(!empty($config['auth'])){
            $this->auth($config['auth']);
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param null $expire
     * @return bool
     */
    public function set($key, $value, $expire = NULL)
    {
        if($expire){
            return parent::setex($key, $expire, $value);
        }
        return parent::set($key, $value);
    }
}