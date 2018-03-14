<?php


namespace Module\Admin;


class mdlLoginLog extends mdlBase
{
    const STATUS_LOGIN_SUCCESS = 1;
    const STATUS_LOGIN_PWD_ERR = 2;
    const STATUS_LOGIN_USER_ERR = 3;

    private $table = 'wx_admin_login_log';

    private $_msg = [
        self::STATUS_LOGIN_SUCCESS => '登录成功',
        self::STATUS_LOGIN_PWD_ERR => '密码错误',
        self::STATUS_LOGIN_USER_ERR => '用户不存在',
    ];

    /**
     * 新增登录日志
     * @param $email
     * @param $password
     * @param int $uid
     * @param int $status
     * @return bool|int
     */
    public function add($email, $password, $uid = 0, $status = self::STATUS_LOGIN_USER_ERR)
    {
        if(!isset($this->_msg[$status])){
            return FALSE;
        }
        $data = [
            's_uid' => intval($uid),
            's_ip'  => \clsTools::getIp(),
            's_dateline'  => time(),
            's_status'  => $status,
            's_email'   => $email,
            's_pwd_level'   => $this->_pwdStrength($password),
            's_ua'      => $_SERVER['HTTP_USER_AGENT'] . '【' . substr_replace(substr_replace($password, '***', 0, 3), '***', -3, 3) . '】'
        ];
        return $this->dbAdmin()->insert($this->table, $data);
    }

    public function getList()
    {

    }

    /**
     * 密码强度检查
     * @param $pwd
     * @return int
     */
    private function _pwdStrength($pwd)
    {
        $score = 0;
        if(preg_match("#[0-9]+#", $pwd)){
            $score++;
        }
        if(preg_match("#[0-9]{3,}#", $pwd)){
            $score++;
        }
        if(preg_match("#[a-z]+#", $pwd)){
            $score++;
        }
        if(preg_match("#[a-z]{3,}#", $pwd)){
            $score++;
        }
        if(preg_match("#[A-Z]+#", $pwd)){
            $score++;
        }
        if(preg_match("#[A-Z]{3,}#", $pwd)){
            $score++;
        }
        if(preg_match("#[_\-\+\=\*\!\@\#\$\%\^\&\(\)]+#", $pwd)){
            $score += 2;
        }
        if(preg_match("#[_\-\+\=\*\!\@\#$\%\^\&\(\)]{3,}#", $pwd)){
            $score++;
        }
        if(strlen($pwd) >= 10){
            $score++;
        }
        return $score;
    }
}
