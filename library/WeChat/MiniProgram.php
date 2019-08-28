<?php


namespace Library\WeChat;



class MiniProgram
{
    // 登录校验地址
    private static $_authUrl = "https://api.weixin.qq.com/sns/jscode2session";

    // 获取小程序码地址
    private static $_wxaqrCodeUrl = "https://api.weixin.qq.com/wxa/getwxacode";

    // 获取小程序接口调用凭证
    private static $_accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token";
    // 获取小程序码，适合需要的码数量极多的业务场景
    private static $_wxaCodeUnlimit = "https://api.weixin.qq.com/wxa/getwxacodeunlimit";

    /**
     * 微信小程序的app_secret和app_id
     */
    const APP_SECRET = "";
    const APP_ID = "";

    const EXPIRE_ACCESS_TOKEN = 7000; // access_token有效时间 2小时,这里用少了200s，提前刷新
    const MINI_PROGRAM_ACCESS_TOKEN = "mini_program_access_token";

    /**
     * 生成微信小程序登录认证地址
     * 请求方法：GET
     * @param $jscode
     * @return string
     */
    public static function makeUrl($jscode)
    {
        return sprintf("%s?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code", self::$_authUrl, self::APP_ID, self::APP_SECRET, $jscode);
    }

    /**
     * 获取用户认证信息
     * @param $code
     * @return array
     */
    public function getUserInfo($code, $iv = "", $encryptedData = "")
    {
        $url = self::makeUrl($code);
        $result = self::send($url);
        $data = $this->_formatData($result);
        if(!isset($result['unionid']) || !$result['unionid']){
            // 如果不存在 unionid，则用session_key解码
            $unionId = self::_descrypt($result['session_key'], $encryptedData, $iv);
            $data['unionid'] = $unionId['unionId'];
            if($data['unionid']){
                $data['errcode'] = 0;
            }
        }
        switch ($data['errcode']){
            case -1:
                // 系统繁忙
                $data["message"] = "服务器繁忙，请稍后再试";
                break;
            case 40029:
                // code无效
                $data["message"] = "无效登录";
                break;
            case 45011:
                // 频率限制
                $data['message'] = "请求太频繁了，请稍后再试";
                break;
            case 0:
                // 请求成功，注册用户信息
                if(($uid = $this->_updateUser($data['unionid'])) <= 0){
                    $data['message'] = "登录失败";
                }else{
                    $data["message"] = "";
                    $data['uid'] = $uid;
                }
                break;
            default:
                // 未知错误
                $data["message"] = "未知错误";
        }
        return $data;
    }

    /**
     * 解密
     * @param $sessionKey
     * @param $encryptData
     * @param $iv
     * @return array
     */
    static private function _descrypt($sessionKey, $encryptData, $iv)
    {
        $unionid = [
            "unionId" => "",
            "message" => "",
        ];
        if(strlen($sessionKey) != 24){
            // 非法的秘钥
            $unionid["message"] = "非法秘钥";
            return $unionid;
        }
        $aesKey = base64_decode($sessionKey);

        if(strlen($iv) != 24){
            // 非法iv
            $unionid["message"] = "非法iv";
            return $unionid;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptData);

        $result = (new \clsDecrypt($aesKey, $aesIV))->aes128cbcDecrypt($aesCipher);
        $dataObj = json_decode($result, 1);
        if(empty($dataObj)){
            // 非法buffer
            $unionid["message"] = "非法buffer1";
            $unionid["json_data"] = $result;
            return $unionid;
        }
        if($dataObj["watermark"]["appid"] != self::APP_ID){
            // 非法buffer
            $unionid["message"] = "非法buffer2";
            return $unionid;
        }

        $data = $dataObj;

        return $data;
    }

    /**
     * 请求登录凭证校验接口
     * @param $url
     * @param string $method
     * @param array $data
     * @return bool|mixed|null
     */
    public static function send($url, $method = "get", $data = [])
    {

        switch ($method){
            case "get":
                $result = \clsCurl::get($url, \clsCurl::RES_TYPE_JSON);
                break;
            case "post":
                $result = \clsCurl::post($url, $data);
                break;
            default:
                $result = [];
        }
        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    private function _formatData($result)
    {
        $data = [
            'unionid' => $result['unionid'] ?: "", // 用户在开放平台唯一标识
            'openid' => $result['openid'] ?: "",
            'session_key' => $result['session_key'] ?: "", // 用于解密用户unionid等数据
            'errMsg' => $result['errMsg'] ?: "",
            'errcode' => $result['errcode'],
            'messaage' => "", // 返回给用户的信息
        ];

        return $data;
    }

    /**
     * 更新注册新用户
     * @param $unionid
     * @return bool
     */
    private function _updateUser($unionid)
    {
        if(!$unionid){
            return 0;
        }
        // 注册新用户

        return ;
    }

    /**
     * 生成微信小程序二维码
     * @param string $path 扫码进入的小程序页面路径
     * @return string
     */
    public function getWXAQRCode($path, $fileName = "")
    {
        $fileName = $fileName ?: "wxacode_" . md5($path) . ".png";
        $filePath = JFRAME_PATH . "/public/res/wechat/images/" . $fileName;
        $qrcodeUrl = SYS_IS_ONLINE ? "your_host_domain/" . $fileName : "your_host_domain/" . $fileName;
        if(file_exists($filePath)){
            return $qrcodeUrl;
        }
        $accessToken = self::_getAccessToken();
        if(!$accessToken){
            // 请求失败
            return $qrcodeUrl;
        }

        // 2. 获取小程序二维码地址
        $postData = [
            "path" => $path,
            "is_hyaline" => true,
            "width" => 1280,
        ];
        $qrcode = self::send(self::_makeWXAQrCodeUrl($accessToken), "post", json_encode($postData, JSON_UNESCAPED_UNICODE));
        $result = json_decode($qrcode, 1);
        switch ($result['errcode']){
            case 45029:
                // 生成码个数总和到达最大个数限制
                return $qrcodeUrl;
                break;
        }
        // 二进制文件流处理
        if(@$fp = fopen($filePath, 'w+')){
            fwrite($fp, $qrcode);
            fclose($fp);
        }else{
            return $qrcodeUrl;
        }
        // 该接口返回的是图片二进制文件流
        // 请求成功
        return $qrcodeUrl;
    }

    /**
     * 生成微信小程序二维码
     * @param string $path 扫码进入的小程序页面路径
     * @return string
     */
    public function getWXAQRCodeUnlimit($path, $code = "")
    {
        $qrcodeUrl = "";
        $accessToken = self::_getAccessToken();
        if(!$accessToken){
            // 请求失败
            return $qrcodeUrl;
        }

        // 2. 获取小程序二维码地址
        $postData = [
            "page" => $path,
            "is_hyaline" => true, // 透明色
            "width" => 1280,
        ];
        if($code){
            $postData["scene"] = $code;
        }
        $qrcode = self::send(self::_makeWXACodeUnlimitUrl($accessToken), "post", json_encode($postData, JSON_UNESCAPED_UNICODE));
        $result = json_decode($qrcode, 1);
        if(isset($result['errcode'])){
            switch ($result['errcode']){
                case 45009:
                    // 调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成
                    return $qrcodeUrl;
                case 41030:
                    // 所传page页面不存在，或者小程序没有发布
                    return $qrcodeUrl;
                case 40169:
                    // 无效的scene
                    return $qrcodeUrl;
                case 40001:
                    // invalid credential, access_token is invalid or not latest hint
                    // 重新刷新key
                    $redisKey = self::MINI_PROGRAM_ACCESS_TOKEN;
                    (new \clsRedis("online"))->del($redisKey);
                    return $qrcodeUrl;
                default:
                    return $qrcodeUrl;
            }
        }
        // 该接口返回的是图片二进制文件流
        // 请求成功
        return $qrcode;
    }

    /**
     * @return bool|string
     */
    private static function _getAccessToken()
    {
        $redisKey = self::MINI_PROGRAM_ACCESS_TOKEN;
        $accessToken = (new \clsRedis("online"))->get($redisKey);
        if($accessToken){
            return $accessToken;
        }
        // 1. 获取access_token数据
        $result = self::send(self::_makeAccessTokenUrl());
        if(isset($result['access_token']) && $result['access_token']){
            // 请求成功
            $accessToken = $result['access_token'];
        }else{
            switch ($result['errcode']){
                case -1:
                    break;
                case 40001:
                    // AppSecret错误或者AppSecret不属于这个小程序，请开发者确认AppSecret的正确性
                    break;
                case 40002:
                    // 请确保grant_type字段值为client_credential
                    break;
                case 40013:
                    // 不合法的 AppID，请开发者检查 AppID 的正确性，避免异常字符，注意大小写
                    break;
                case 40164:
                    // 调用接口的IP地址不在白名单中，请在接口IP白名单中进行设置
                    break;
                case 0:
                    // 请求成功
                    $accessToken = $result['access_token'];
                    break;
                default:
                    $accessToken = "";
            }
        }

        if(!$accessToken){
            return $accessToken;
        }
        // access_token有效时间为两小时，保存
        (new \clsRedis("online"))->set($redisKey, $accessToken, self::EXPIRE_ACCESS_TOKEN);
        return $accessToken;
    }

    /**
     * 生成获取小程序二维码地址
     * 请求方法：POST
     * @param $accessToken
     * @return string
     */
    private static function _makeWXAQrCodeUrl($accessToken)
    {
        return sprintf("%s?access_token=%s", self::$_wxaqrCodeUrl, $accessToken);
    }

    /**
     * 小程序调用凭证地址
     * 请求方法：GET
     * @return string
     */
    private static function _makeAccessTokenUrl()
    {
        return sprintf("%s?grant_type=client_credential&appid=%s&secret=%s", self::$_accessTokenUrl, self::APP_ID, self::APP_SECRET);
    }

    /**
     * 生成获取小程序码地址
     * 请求方法： POST
     * @param $accessToken
     * @return string
     */
    private static function _makeWXACodeUnlimitUrl($accessToken)
    {
        return sprintf("%s?access_token=%s", self::$_wxaCodeUnlimit, $accessToken);
    }
}