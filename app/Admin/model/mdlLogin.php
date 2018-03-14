<?php


namespace Module\Admin;



class mdlLogin extends mdlSysUser
{

    /**
     * 系统管理员会话加密密钥
     */
    const SYS_SERVER_ADMIN_SESSION_KEY = 'gi23@#f*%p$3s#';

    /**
     * cookie路径
     * @var string
     */
    private $_cookiePath = "/";

    /**
     * cookie名称
     * @var string
     */
    private $_cookieName = "_wx_hr_admin_v2_";

    /**
     * 过期时间
     * @var int
     */
    private $_expire = 3600;

    public function __construct()
    {
        parent::__construct();
        $this->_expire = mktime(0, 0, 0) + 86400 - time();
    }

    /**
     * 登录验证
     * @param $email
     * @param $password
     * @param bool $doLog
     * @return array|bool
     */
    public function verify($email, $password, $doLog = TRUE)
    {
        $password = trim($password);
        $email = $this->_filterEmail($email);
        $user = $this->get(0, $email);
        if(!$user){
            // 用户不存在
            $this->_writeLog($email, $password, 0, mdlLoginLog::STATUS_LOGIN_USER_ERR, $doLog);
            return "98:用户不存在";
        }
        if($user['status'] != self::STATUS_NORMAL){
            // 账号不正常 （审核未通过 或 账号已弃用）
            return "97:账号不正常";
        }
        $passwd = $this->encryptPwd($password, $user['salt']);
        if($user['password'] !== $passwd){
            // 密码不正确
            $this->_writeLog($email, $password, 0, mdlLoginLog::STATUS_LOGIN_PWD_ERR, $doLog);
            return "96:账号或密码错误";
        }

        $user['salt'] = $this->salt(6);
        // 更新登陆时间
        $this->updateLoginTime($user['id'], $password, $user['salt']);
        // 记录登录日志
        $this->_writeLog($email, $password, $user['id'], mdlLoginLog::STATUS_LOGIN_SUCCESS, $doLog);
        return $user;
    }

    /**
     * @param $email
     * @return null|string|string[]
     */
    private function _filterEmail($email)
    {
        return preg_replace('#[^\w@\-\.]+#is', '', $email);
    }

    /**
     * 记录登录日志
     * @param $email
     * @param $password
     * @param $uid
     * @param $status
     * @param bool|TRUE $doLog
     */
    private function _writeLog($email, $password, $uid, $status, $doLog = TRUE)
    {
        if($doLog){
            mdlLoginLog::instance()->add($email, $password, $uid, $status);
        }
    }

    public function cookieName()
    {
        return sprintf("%s%s_", $this->_cookieName, SYS_ENV);
    }

    /**
     * session操作
     * @param string $op
     * @param array $sessionInfo
     * @return array|mixed
     */
    public function session($op = 'set', $sessionInfo = [])
    {
        $uuid = $this->_uuid();
        $lastTime = substr($uuid, 0, 10);
        $cookie = substr($uuid, 11);
        $salt = substr($cookie, 32, 6);
        $token = substr($cookie, 0, 32);
        $adminId = substr($cookie, 38);
        if($op != 'set' && $token != $this->_token($adminId, $salt)){
            return [];
        }
        $key = $this->_getRedisKey(isset($sessionInfo['id']) && $sessionInfo['id'] ? $sessionInfo['id'] : $adminId);
        switch($op){
            case 'set':
                $sessionInfo = $this->_set($key, $sessionInfo);break;
            case 'get':
                $sessionInfo = $this->_get($key, $lastTime);break;
            case 'reset':
                $this->_reset($key);break;
        }

        return $sessionInfo;
    }

    /**
     * 重置cookie
     * @param $key
     */
    private function _reset($key)
    {
        $this->redis('admin')->delete($key);
        setcookie($this->cookieName(), "", 0, $this->_cookiePath);
    }

    /**
     * 设置cookie
     * @param $key
     * @param $sessionInfo
     * @return mixed
     */
    private function _set($key, $sessionInfo)
    {
        // 设置cookie过期时间
        $ttlCookie = time() + $this->_expire;
        $salt = $this->salt(6);
        $sessionInfo['last_login_time'] = $sessionInfo['last_login_time'] ?: strtotime('2015-01-01 00:00:00');
        $cookie = $sessionInfo['last_login_time'] . "_" . $this->_token($sessionInfo['id'], $salt) . $salt . $sessionInfo['id'];
        $this->redis('admin')->set($key, json_encode($sessionInfo, JSON_UNESCAPED_UNICODE), $this->_expire);
        setcookie($this->cookieName(), $cookie, $ttlCookie, $this->_cookiePath);
        return $sessionInfo;
    }

    /**
     * 获取cookie
     * @param $key
     * @param $lastTime
     * @return mixed
     */
    private function _get($key, $lastTime)
    {
        $sessionInfo = @json_decode($this->redis('admin')->get($key), 1);
        if(!is_array($sessionInfo) && ($lastTime != $sessionInfo['last_login_time'])){
            $this->redis('admin')->delete($key);
            setcookie($this->cookieName(), "", 0, $this->_cookiePath);
        }
        return $sessionInfo;
    }

    /**
     * 获取redis key
     * @param $adminId
     * @return string
     */
    private function _getRedisKey($adminId)
    {
        return "wx_admin_login_key_" . $adminId;
    }

    /**
     * @param $adminId
     * @param $salt
     * @return string
     */
    private function _token($adminId, $salt)
    {
        if(!SYS_IS_ONLINE){
            return md5($salt . $adminId);
        }
        $ip = \clsTools::getIp();
        $userAgent = $_POST['_userAgent_'] ?: $_SERVER['HTTP_USER_AGENT'];
        $token = [
            $ip,
            $userAgent,
            self::SYS_SERVER_ADMIN_SESSION_KEY,
            $salt,
            $adminId
        ];
        $token = implode("", $token);
        return md5($token);
    }

    /**
     * 获取PHPSESSID兼容COOKIE
     * @return mixed
     */
    private function _uuid()
    {
        $uuid = isset($_COOKIE[$this->cookieName()]) ? $_COOKIE[$this->cookieName()] : '';
        if(!$uuid){
            $uuid = isset($_POST[$this->cookieName()]) ? $_POST[$this->cookieName()] : '';
        }
        $uuid = preg_replace('#[^\w]+#is', '', $uuid);
        return $uuid;
    }

    /**
     * @param $username
     * @param $email
     * @param $password1
     * @param $password2
     * @return bool
     */
    public function register($username, $email, $password1, $password2)
    {
        // 邮箱格式
        $email = $this->_filterEmail($email);
        if(!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email)){
            return "101:邮箱格式错误";
        }
        // 判断密码一致性
        if(!$this->_matchPwd($password1, $password2)){  // 密码不一致
            return "101:密码不一致";
        }

        // 判断用户名是否已存在
        if(!$this->checkName($username)){ // 用户名已存在
            return "101:用户名已存在";
        }

        // 判断邮箱是否已存在
        if(!$this->checkEmail($email)){ // 邮箱已存在
            return "101:邮箱已存在";
        }

        // 注册用户信息
        $res = $this->add($username, $email, $password1, self::STATUS_AUDIT, self::TYPE_COMMON);
        if($res){
            // 注册成功
            return "100:注册成功";
        }
        return "99:注册失败,请重新注册";
    }

    private function _matchPwd($password1, $password2)
    {
        if(trim($password1) == trim($password2)){
            return true;
        }
        return false;
    }

    /**
     * 更新用户信息
     * @param $id
     * @param $username
     * @param $oldName
     * @param $email
     * @param $opassword
     * @param $password
     * @param $rpassword
     * @return string
     */
    public function updateUserInfo($id, $username, $oldName, $email, $opassword, $password, $rpassword)
    {
        $id = intval($id);
        if(!$id){
            return "101:缺少信息";
        }
        // 邮箱格式
        $email = $this->_filterEmail($email);
        if(!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email)){
            return "101:邮箱格式错误";
        }
        // 判断密码一致性
        if(!$this->_matchPwd($password, $rpassword)){
            return "101:密码不一致";
        }

        // 判断用户名是否已存在
        if(!$this->checkName($oldName, $id)){
            return "101:用户名不存在";
        }

        if(!$this->checkName($username, $id)){
            return "101:新用户名已存在";
        }

        $old = $this->get($id);
        if(!$old){
            return '101:用户不存在';
        }

        // 2. 检查旧密码
        if(!$this->checkPwd($opassword, $id)){
            return '103:原密码错误';
        }

        // 修改用户信息
        $res = $this->edit($id, $username, $email, $password);
        if($res === FALSE){
            return "99:保存失败";
        }
        return "100:保存成功";
    }
}

