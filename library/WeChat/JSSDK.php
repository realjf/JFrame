<?php


namespace Library\WeChat;


class JSSDK
{
    /**
     * 公众号 appid
     * @var string
     */
    private $_appId = "";
    /**
     * 公众号秘钥
     * @var string
     */
    private $_appSecret = "";

    /**
     * accessToken获取地址
     * @var string
     */
    private $_access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";

    /**
     * 获取ticket
     * @var string
     */
    private $_api_ticket_url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s";

    /**
     * JSSDK constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return array
     */
    public function getSignPackage()
    {
        $jsapiTicket = $this->_getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->_createNonceStr();

        // 这里参数的顺序要按照 key 值ASCII 码升序排序
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $jsapiTicket, $nonceStr, $timestamp, $url);

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->_appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string,
        );

        return $signPackage;
    }

    /**
     * @param int $length
     * @return string
     */
    private function _createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @return mixed
     */
    private function _getJsApiTicket()
    {
        $data = json_decode($this->_get_php_file("jsapi_ticket.php"));
        if($data->expire_time < time()){
            $accessToken = $this->_getAccessToken();
            $ticket = $this->_getTicket($accessToken);
            if($ticket){
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->_set_php_file("jsapi_ticket.php", json_encode($data));
            }
        }else{
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    /**
     * @param $accessToken
     * @return mixed
     */
    private function _getTicket($accessToken)
    {
        $url = sprintf($this->_api_ticket_url, $accessToken);
        $res = \clsCurl::get($url, \clsCurl::RES_TYPE_JSON);
        $ticket = $res["ticket"];
        return $ticket;
    }

    private function _getAccessToken()
    {
        $data = json_decode($this->_get_php_file("access_token.php"));
        if($data->expire_time < time()){
            $url = sprintf($this->_access_token_url, $this->_appId, $this->_appSecret);
            $res = \clsCurl::get($url, \clsCurl::RES_TYPE_JSON);
            $accessToken = $res["access_token"];
            if($accessToken){
                $data->expire_time = time() + 7000;
                $data->access_token = $accessToken;
                $this->_set_php_file("access_token.php", json_encode($data));
            }
        }else{
            $accessToken = $data->access_token;
        }

        return $accessToken;
    }

    /**
     * 获取文件
     * @param $filename
     * @return string
     */
    private function _get_php_file($filename)
    {
        return trim(substr(file_get_contents(__DIR__ . "/" . $filename), 15));
    }

    /**
     * @param $filename
     * @param $content
     */
    private function _set_php_file($filename, $content)
    {
        $fp = fopen(__DIR__ . "/" . $filename, "w");
        fwrite($fp, "<?php exit(); ?>" . $content);
        fclose($fp);
    }

    private function _httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}