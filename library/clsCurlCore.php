<?php

/**
 * Created by JetBrains PhpStorm.
 * User: flycat
 * Date: 10/30/12
 * Time: 4:42 PM
 * To change this template use File | Settings | File Templates.
 */
class clsCurlCore
{
    /**
     * 不记录任何log
     */
    const DEBUG_NONE = 0b0000; // 0

    /**
     * 记录错误的请求,curl错误
     */
    const DEBUG_ERROR = 0b0100; // 4

    /**
     * 记录HTTP的错误请求，返回http_code不是200
     */
    const DEBUG_HTTP_ERROR = 0b0010; // 2

    /**
     * 记录正常请求
     */
    const DEBUG_RESULT = 0b0001; // 1

    /**
     * 记录所有请求
     */
    const DEBUG_ALL = 0b0111; // 7

    /**
     * 是否是上传文件的请求
     */
    const OPT_FILES = 99999;

    /**
     * @var array
     */
    private $_options = array();

    /**
     * @var array
     */
    private $_optionsDefault = array();

    /**
     * @var string
     */
    private $_result = NULL;

    /**
     * @var int
     */
    private $_errno = 0;

    /**
     * @var string
     */
    private $_error = '';

    /**
     * @var array|null
     */
    private $_info = NULL;

    /**
     * 开启调试级别
     * @var int
     */
    private $_debug = 0x0000;

    /**
     * 调试输出文件
     * @var string
     */
    private $_debugFile = '/tmp/curl_';

    /**
     * 域名
     * @var string
     */
    private $_host = '';

    /**
     * @param int    $debug -- 调试级别
     * @param string $prefix -- 调试信息输出文件前缀，包含目录
     *                       例如前缀是  /tmp/abc,  输出文件名是  /tmp/abc_%Y%m%d.log  和 /tmp/abc_%Y%m%d.err
     *                       log 记录正常请求(http_code = 200)   err 记录异常请求（http_code != 200）
     * @return self
     */
    static public function instance($debug = NULL, $prefix = NULL)
    {
        static $instance = NULL;
        if(!($instance instanceof self)){
            $instance = new self($debug, $prefix);
        } else{
            $instance->reset();
            $instance->setDebug($debug, $prefix);
        }
        return $instance;
    }

    /**
     * @param int    $debug -- 调试级别
     * @param string $prefix -- 调试信息输出文件前缀，包含目录
     *                       例如前缀是  /tmp/abc,  输出文件名是  /tmp/abc_%Y%m%d.log  和 /tmp/abc_%Y%m%d.err
     *                       log 记录正常请求(http_code = 200)   err 记录异常请求（http_code != 200）
     */
    public function __construct($debug = NULL, $prefix = NULL)
    {
        $options = array();
        $options[CURLOPT_URL] = NULL;
        $options[CURLOPT_POST] = 0;
        $options[CURLOPT_POSTFIELDS] = NULL;
        $options[CURLOPT_RETURNTRANSFER] = 1;
        $options[CURLOPT_NOBODY] = 0;
        $options[CURLOPT_USERAGENT] = NULL;
        $options[CURLOPT_TIMEOUT] = 10;
        $options[CURLOPT_CONNECTTIMEOUT] = 5;
        $options[CURLOPT_REFERER] = '';
        $options[CURLOPT_COOKIE] = NULL;
        $options[CURLOPT_HEADER] = FALSE;
        $options[CURLOPT_HTTPHEADER] = array();
        $this->_optionsDefault = $options;
        $this->reset();
        $this->setDebug($debug, $prefix);
    }

    /**
     * @return int
     */
    public function send()
    {
        if(isset($this->_options[CURLOPT_UPLOAD]) && $this->_options[CURLOPT_UPLOAD]){
            $this->_options[CURLOPT_POST] = 1;
            unset($this->_options[CURLOPT_UPLOAD]);
            $this->_options[CURLOPT_POSTFIELDS] = $this->_makePostData($this->_options[CURLOPT_POSTFIELDS]);
        } elseif(isset($this->_options[self::OPT_FILES]) && $this->_options[self::OPT_FILES]){
            $this->_options[CURLOPT_POST] = 1;
            $this->_options[CURLOPT_POSTFIELDS] = $this->_makePostData($this->_options[CURLOPT_POSTFIELDS], $this->_options[self::OPT_FILES]);
        } elseif(isset($this->_options[CURLOPT_POST]) && $this->_options[CURLOPT_POST]){
            if(is_array($this->_options[CURLOPT_POSTFIELDS])){
                $this->_options[CURLOPT_POSTFIELDS] = $this->_makePostData($this->_options[CURLOPT_POSTFIELDS]);
            }
        } else{
            unset($this->_options[CURLOPT_POSTFIELDS]);
        }
        $curl = curl_init();
        curl_setopt_array($curl, $this->_options);
        $this->_result = curl_exec($curl);
        $this->_info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        $code = $this->code();
        if($errno > 0){
            if(in_array($errno, [6, 7])){
                $data = [
                    'url'   => $this->_info['url'],
                    'code'  => $code,
                    'errno' => $errno,
                    'error' => $this->error(),
                ];
                return $this->_retry();
            } else{
                $this->_setError($errno, curl_error($curl));
                $this->_debug(self::DEBUG_ERROR);
            }
        } else{
            if($code != 200){
                $this->_setError($code, $this->_result);
                $this->_debug(self::DEBUG_HTTP_ERROR);
            } else{
                $this->_setError(0, '');
                $this->_debug(self::DEBUG_RESULT);
            }
        }
        curl_close($curl);
        return $this->error();
    }

    /**
     * 重试请求
     * @return string
     */
    private function _retry()
    {
        $curl = curl_init();
        curl_setopt_array($curl, $this->_options);
        $this->_result = curl_exec($curl);
        $this->_info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        if($errno > 0){
            $this->_setError($errno, curl_error($curl));
            $this->_debug(self::DEBUG_ERROR);
        } else{
            $code = $this->code();
            if($code != 200){
                $this->_setError($code, $this->_result);
                $this->_debug(self::DEBUG_HTTP_ERROR);
            } else{
                $this->_setError(0, '');
                $this->_debug(self::DEBUG_RESULT);
            }
        }
        curl_close($curl);
        return $this->error();
    }

    /**
     * 设置HOST
     * @param $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setOptions($data)
    {
        foreach($data as $name => $value){
            $this->setOption($name, $value);
        }
    }

    /**
     * @return null|string
     */
    public function result()
    {
        return $this->_result;
    }

    /**
     * @return string
     */
    public function error()
    {
        return $this->_error;
    }

    /**
     * @return int
     */
    public function errno()
    {
        return $this->_errno;
    }

    /**
     * @return array
     */
    public function info()
    {
        return $this->_info;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->_setError();
        $this->_info = NULL;
        $this->_result = NULL;
        $this->_options = $this->_optionsDefault;
    }

    /**
     * @return int
     */
    public function code()
    {
        return isset($this->_info['http_code']) ? $this->_info['http_code'] : 0;
    }

    /**
     * @param int    $errno
     * @param string $error
     * @return int
     */
    private function _setError($errno = 0, $error = '')
    {
        $this->_errno = $errno;
        $this->_error = $error;
    }

    /**
     * @param int    $debug
     * @param string $prefix
     */
    public function setDebug($debug = NULL, $prefix = NULL)
    {
        if(is_null($debug)){
            $debug = self::DEBUG_ERROR | self::DEBUG_HTTP_ERROR;
        }
        $this->_debug = $debug;
        if(!is_null($prefix)){
            $this->_debugFile = $prefix;
        }
    }

    /**
     * @param int $level
     * @return bool
     */
    private function _debug($level = self::DEBUG_ERROR)
    {
        if(!($level & $this->_debug)){
            return FALSE;
        }
        $data = array();
        $data['url'] = $this->_info['url'];
        $data['code'] = $this->code();
        $data['errno'] = $this->errno();
        if(!in_array($this->code(), [404, 301, 302])){
            $data['error'] = $this->error();
        }
        $data['uri'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if($this->_host){
            $data['host'] = $this->_host;
        }
        if($this->code() >= 200 and $this->code() < 400){
            exit;
        }
        return TRUE;
    }

    /**
     * @param array      $data
     * @param array|null $files
     * @return array
     */
    private function _makePostData($data, $files = NULL)
    {
        if($files){
            $files = $this->_mkUploadFiles($files);
            if($files){
                $data = array_merge($data, $files);
            }
        }
        return $this->_makePostDataValue(NULL, $data);
    }

    /**
     * @param array  $result
     * @param array  $data
     * @param string $prefix
     * @return array
     */
    private function _makePostDataValue($result, $data, $prefix = '')
    {
        if(empty($result)){
            $result = array();
        }
        if(empty($data)){
            return $result;
        }
        foreach($data as $k => $v){
            $kk = $prefix ? "{$prefix}[{$k}]" : $k;
            if(is_array($v)){
                $result = $this->_makePostDataValue($result, $v, $kk);
            } else{
                $result[$kk] = $v;
            }
        }
        return $result;
    }

    /**
     * @param array|string $files
     * @return array|bool|string
     */
    private function _mkUploadFiles($files)
    {
        if(is_array($files)){
            foreach($files as $fk => $fv){
                $fv = $this->_mkUploadFiles($fv);
                if(empty($fv)){
                    unset($files[$fk]);
                } else{
                    $files[$fk] = $fv;
                }
            }
            return $files;
        }
        $files = realpath($files);
        return $files ? '@' . $files : FALSE;
    }
}
