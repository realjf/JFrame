<?php

/**
 * Created by JetBrains PhpStorm.
 * User: flycat
 * Date: 11/5/12
 * Time: 6:50 PM
 * To change this template use File | Settings | File Templates.
 */
class clsCurl
{
    /**
     *
     */
    const RES_TYPE_STRING = 0;

    /**
     *
     */
    const RES_TYPE_JSON = 1;

    /**
     *
     */
    const RES_TYPE_JSON_OBJECT = 2;

    /**
     *
     */
    const RES_TYPE_PHP = 3;

    /**
     * @var int
     */
    static private $_errno = 0;

    /**
     * @var string
     */
    static private $_error = '';

    /**
     * @var string
     */
    static private $_original = '';

    /**
     * @var array
     */
    static private $_info = array();

    /**
     * @var int
     */
    static private $_code = 200;

    /**
     * 开启调试级别
     * @var int
     */
    static private $_debug = NULL;

    /**
     * 上次调试
     * @var null
     */
    static private $_lastDebug = NULL;

    /**
     * @param string $url
     * @param string $type
     * @param array  $options
     * @return mixed|null
     */
    static public function get($url, $type = NULL, $options = NULL)
    {
        if(!is_array($options)){
            $options = array();
        }
        $options[CURLOPT_POST] = 0;
        unset($options[CURLOPT_POSTFIELDS]);
        return self::_send($url, $type, $options);
    }

    /**
     * @param string $url
     * @param null   $data
     * @param string $type
     * @param array  $options
     * @return mixed|null
     */
    static public function post($url, $data = NULL, $type = NULL, $options = NULL)
    {
        if(!is_array($options)){
            $options = array();
        }
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $data;
        return self::_send($url, $type, $options);
    }

    /**
     * @param string $url
     * @param null   $data
     * @param string $type
     * @param array  $options
     * @return mixed|null
     */
    static public function upload($url, $data = NULL, $type = NULL, $options = NULL)
    {
        if(!is_array($options)){
            $options = array();
        }
        $options[CURLOPT_UPLOAD] = 1;
        $options[CURLOPT_POSTFIELDS] = $data;
        return self::_send($url, $type, $options);
    }

    /**
     * @return int
     */
    static public function errno()
    {
        return self::$_errno;
    }

    /**
     * @return string
     */
    static public function error()
    {
        return self::$_error;
    }

    /**
     * @return string
     */
    static public function original()
    {
        return self::$_original;
    }

    /**
     * 设置调试,只单次请求有效
     * @param null $debug
     * @return null
     */
    static public function setDebug($debug = NULL)
    {
        if(is_null($debug) || !is_int($debug) || $debug < 0 || $debug > 7){
            $debug = NULL;
        }
        self::$_lastDebug = self::$_debug;
        return self::$_debug = $debug;
    }


    /**
     * @param string $url
     * @param int    $type
     * @param array  $options
     * @return mixed|null
     */
    static private function _send($url, $type, $options)
    {
        $options[CURLOPT_URL] = $url;

        $curl = clsCurlCore::instance(self::$_debug);
        $curl->setOptions($options);
        $curl->send();

        self::$_code = $curl->code();
        self::_setError($curl->errno(), $curl->error());
        self::$_original = $curl->result();
        self::$_info = $curl->info();
        $result = self::_result($type);

        $curl = NULL;
        unset($curl);
        if(self::$_lastDebug !== self::$_debug){
            self::setDebug(self::$_lastDebug);
        }
        return $result;
    }

    /**
     * @param int    $errno
     * @param string $error
     * @return bool
     */
    static private function _setError($errno, $error)
    {
        self::$_errno = $errno;
        self::$_error = $error;
    }

    /**
     * @param null|int $type
     * @return mixed|null
     */
    static private function _result($type = NULL)
    {
        if(self::$_errno > 0){
            return FALSE;
        }
        $original = self::$_original;
        $decoded = 1;
        if($type == self::RES_TYPE_JSON){
            $result = @json_decode($original, TRUE);
        } elseif($type == self::RES_TYPE_JSON_OBJECT){
            $result = @json_decode($original, FALSE);
        } elseif($type == self::RES_TYPE_PHP){
            $result = @unserialize($original);
        } else{
            $decoded = 0;
            $result = $original;
        }

        if($decoded && $result === FALSE){
            self::_setError(999, 'Data Parse Error.');
            return FALSE;
        }
        return $result;
    }

    /**
     * @return int
     */
    static public function lastCode()
    {
        return self::$_code;
    }

    /**
     * @return array
     */
    static public function info()
    {
        return self::$_info;
    }
}
