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
}