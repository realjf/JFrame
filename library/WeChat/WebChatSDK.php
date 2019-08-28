<?php
/**
 * 微信授权相关接口
 * FileName: WeChatSDK.php
 */
namespace Library\WeChat;

class WeChatSDK{

    //高级功能-》开发者模式-》获取
    /**
     * 微信公众平台appId
     * @var string
     */
    private $app_id = '';
    private $app_secret = '';
    /**
     * 微信网站应用appId
     * @var string
     */
    private $web_app_id = '';
    private $web_app_secret = '';


    private $access_token = '';
    public  $open_id = '';
    private $refresh_token = '';

    /**
     * 微信接口-获取用户基本信息
     * @var string
     */
    private $_userInfoUrl = 'https://api.weixin.qq.com/sns/userinfo';
    /**
     * 微信接口-获取用户unionid信息
     * @var string
     */
    private $_userUnionIdUrl = 'https://api.weixin.qq.com/cgi-bin/user/info';

    /**
     * 微信oauth2网页授权认证接口
     */
    private $_oauth2Url = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     * 微信oauth2获取access_token接口
     * @var string
     */
    private $_oauth2AccessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * token加密key
     * @var string
     */
    private static $_encryptKey = '';

    private static $_instance = [];

    /**
     * 实例
     * @param string $val
     * @return null|WeChatSDK
     */
    public static function instance($val = '')
    {
        $class = get_called_class();
        $key = $class;
        if($val){
            $key .= '_' . $val;
        }
        if(isset(self::$_instance[$key]) && self::$_instance[$key] instanceof $class){
            return self::$_instance[$key];
        }
        return self::$_instance[$key] = new self;
    }

    /**
     * 构造函数
     */
    public function __construct()
    {

    }

    /**
     * 获取token
     * @return string
     */
    public static function token()
    {
        $ip = \clsTools::getIp(1);
        $ua = \clsVars::vServer('HTTP_USER_AGENT');
        $time = time();
        $salt = \clsTools::randString(6);
        $data = [
            $ip,
            self::$_encryptKey,
            $ua,
            $time,
            $salt,
        ];
        $string = implode('|', $data);
        return $time . '_' . md5($string) . $salt;
    }

    /**
     * 验证token
     * @param $token
     * @return bool
     */
    static public function checkToken($token)
    {
        $ip = \clsTools::getIp(1);
        $ua = \clsVars::vServer('HTTP_USER_AGENT');
        $time = substr($token, 0, 10);
        $salt = substr($token, -6, 6);
        $signString = substr($token, 11, 32);
        $data = [
            $ip,
            self::$_encryptKey,
            $ua,
            $time,
            $salt,
        ];
        $signedString = implode('|', $data);
        if($signedString == $signString){
            return true;
        }
        return false;
    }

    /**
     * @param $redirect_uri
     * @param $state
     * @return string
     */
    public function getOauth2AuthorizeUrl($redirect_uri, $state)
    {
        // 线上环境返回验证token
        if(SYS_ENV_IS_ONLINE){
            if($state){
                $state .= '&';
            }
            $state .= 'token=' . $this->token();
        }
        return sprintf('%s?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s#wechat_redirect',
            $this->_oauth2Url, $this->app_id, $redirect_uri, $state);
    }

    /**
     * 获取用户信息接口
     * @param string $code
     * @param string $from
     * @return bool|mixed
     */
    public function getUserInfo($code = '', $from = '')
    {
        if($code){
            $res = $this->get_access_token($code, $from);
            if($res){
                return $this->get_user_info($this->access_token, $this->open_id, $this->_userInfoUrl);
            }
        }
        return FALSE;
    }

    /**
     * 获取用户基本信息(包含unionId机制)
     * @param string $code
     * @return bool|mixed
     */
    public function getUserUnionId($code = '')
    {
        if($code){
            $res = $this->get_access_token($code);
            if($res){
                return $this->get_user_info($this->access_token, $this->open_id, $this->_userUnionIdUrl);
            }
        }
        return FALSE;
    }

    /**
     * 获取微信授权链接
     *
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     * @return string
     */
    public function get_authorize_url($redirect_uri = '', $state = '')
    {
        $redirect_uri = urlencode($redirect_uri);
        $uri = sprintf("%s?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s#wechat_redirect", $this->_oauth2Url, $this->app_id, $redirect_uri, $state);
        $data = $this->http($uri, 'GET');
        if($data[0] == 200){
            return $data[1];
        }else{
            return "";
        }
    }

    /**
     * 获取授权token
     *
     * @param string $code 通过get_authorize_url获取到的code
     * @return bool|mixed
     */
    private function get_access_token($code = '', $from = '')
    {
        if($from == 'qrcode'){
            // web授权页面登陆
            $token_url = sprintf("%s?appid=%s&secret=%s&code=%s&grant_type=authorization_code", $this->_oauth2AccessTokenUrl, $this->web_app_id, $this->web_app_secret, $code);
        }else{
            // 微信公众号内登陆
            $token_url = sprintf("%s?appid=%s&secret=%s&code=%s&grant_type=authorization_code", $this->_oauth2AccessTokenUrl, $this->app_id, $this->app_secret, $code);
        }

        $token_data = $this->http($token_url, 'GET');

        if($token_data[0] == 200)
        {
            $res = json_decode($token_data[1], TRUE);
            $this->access_token = $res['access_token'];
            $this->open_id = $res['openid'];
            $this->refresh_token = $res['refresh_token'];
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     * @return bool|mixed
     */
    private function get_user_info($access_token = '', $open_id = '', $url = '')
    {
        if($access_token && $open_id)
        {
            $info_url = $url . "?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url, 'GET');

            if($info_data[0] == 200)
            {
                return json_decode($info_data[1], TRUE);
            }
        }
        return FALSE;
    }

    /**
     * 获取网页授权应用appid
     * @return string
     */
    public function getWebAppId()
    {
        return $this->web_app_id;
    }

    /**
     * @param $url
     * @param $method
     * @param null $postfields
     * @param array $headers
     * @param bool|false $debug
     * @return array
     */
    private function http($url, $method, $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
//        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
//        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers){
            curl_setopt($ci, CURLOPT_HEADER, $headers);
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }

}
