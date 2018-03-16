<?php


namespace Module\Admin;


class ctlLogin extends ctlBase
{
    /**
     * 登录
     */
    public function funcIndex()
    {
        $loginInfo = mdlLogin::instance()->session('get');
        if($loginInfo && $loginInfo['id']){
            $this->redirect('/admin/index-index.html');
        }else{
            $formData = \clsVars::post('data')->toArray();
            $formData = \clsTools::slashes($formData, 1);
            if($formData){
                // 登录操作
                $email = $formData['username'];
                $password = $formData['password'];
                // @todo 验证码功能

                // 账号密码验证
                $mdlLogin = mdlLogin::instance();
                $info = $mdlLogin->verify($email, $password);
                if(is_array($info)){
                    unset($info['password']);
                    $mdlLogin->session('set', $info);
                    $response = [
                        'uid' => $info['id'],
                        'name' => $info['name'],
                    ];
                    // 保存信息备用
                    $this->__curMember = $info;
                    $this->response(100, $response, '登录成功');
                }
                $this->output($info);
            }else{
                $this->display('login.html');
            }
        }
    }

    /**
     * 登出
     */
    public function funcOut()
    {
        mdlLogin::instance()->session('reset');
        $this->redirect('/admin/login-index.html');
    }

    /**
     * 注册
     */
    public function funcRegister()
    {
        $formData = \clsVars::post('data')->toArray();
        if($formData){
            $formData = \clsTools::slashes($formData, 1);
            $username = $formData['username'];
            $password = $formData['password'];
            $rpassword = $formData['rpassword'];
            $email = $formData['email'];

            if(!$username || !$password || !$rpassword || !$email){
                $this->response('101:必填项为空');
            }
            $res = mdlLogin::instance()->register($username, $email, $password, $rpassword);
            $this->output($res);
        }else{
            $this->display('register.html');
        }
    }

    /**
     * 忘记密码
     */
    public function funcReset()
    {

    }
}