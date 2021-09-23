<?php


namespace Module\Index;

use Library\WeChat\MiniProgram;

class ctlIndex extends ctlBase
{
    public function funcIndex(){
        $this->display("base.html", ['name' => 'bobby']);
    }

    /**
     * 小程序登录操作
     */
    public function funcLogin()
    {
        $code = \clsVars::request('js_code')->toString(true);
        $code = \clsTools::removeXss($code); // 过滤
        $iv = \clsVars::request('iv')->toString(true);
        $encryptedData = \clsVars::request('encryptedData')->toString(true);
        $avatarUrl = \clsVars::request("avatarUrl")->toString(true); // 用户头像
        $nickName = \clsVars::request("nickName")->toString(true); // 用户昵称
        $nickName = $nickName ? base64_decode($nickName) : "";
        if($nickName && $nickName != "微信用户"){
            $nickName = base64_encode($nickName);
        }else{
            $nickName = "";
        }

        if (!$code) {
            $this->response(101, [], "无效参数");
        }
        if(SYS_IS_ONLINE){
            $result = (new MiniProgram())->getUserInfo($code, $iv, $encryptedData, $nickName, $avatarUrl);
        }else{
            
        }
        
        if($result['message']){
            $this->response(99, [], $result['message']);
        }
        // 注册
        $this->response(100, [$this->__cookie], "登录成功");
    }


    /**
     * 获取二维码
     */
    public function funcGetQrCode()
    {
        $code = \clsVars::get("code")->toString(true);
        $id = \clsVars::get("id")->toInt(); // 职位id
        $qrcode = "";
        $url = "";
        if($url){
            return \Library\Phpqrcode\QrCodeSdk::png($url); // 生成微信端招聘页二维码
        }
        if($qrcode){
            header("Content-Type: image/png; application/octet-stream");
            echo $qrcode;
            exit();
        }else{
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit();
        }
    }
}