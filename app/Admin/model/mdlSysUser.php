<?php


namespace Module\Admin;


class mdlSysUser extends mdlBase
{
    const STATUS_TRASH = 0; // 弃用
    const STATUS_NORMAL = 1; // 正常
    const STATUS_AUDIT = 2; // 待审核
    const STATUS_REJECT = 3; // 拒绝通过

    const TYPE_COMMON = 0; // 普通
    const TYPE_SUPER = 1; // 超管

    private $table = 'wx_sys_user';

    /**
     * @param array $cond
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function getList($cond = [], $page = 1, $limit = 20, $order = 's_id desc')
    {
        $data = $this->dbAdmin()->fetchWithPage($this->table, '*', $cond, $order, $limit, $page);
        $data['data'] = $this->fieldPrefixRm($data['data']);
        return $data;
    }

    /**
     * 获取所有的跟进人
     * @return array
     */
    public function getAllFollows()
    {
        $data = $this->dbAdmin()->fetch($this->table, '*', ['s_type !=' . self::TYPE_SUPER]);
        $data = $this->fieldPrefixRm($data);
        $data = \clsTools::mkKeyValue($data, 'id', 'name');
        return $data;
    }

    /**
     * 获取用户基本信息
     * @param $id
     * @param string $email
     * @return array|bool
     */
    public function get($id, $email = '')
    {
        $cond = [];
        $id = intval($id);
        if($id){
            $cond[] = sprintf("s_id=%d", $id);
        }
        if($email){
            $cond[] = sprintf("s_email='%s'", $this->dbAdmin()->slashes($email));
        }
        if(empty($cond)){
            return FALSE;
        }
        $user = $this->dbAdmin()->select($this->table, '*', $cond);
        return $this->fieldPrefixRm($user);
    }

    /**
     * 添加新用户
     * @param $name
     * @param $email
     * @param $password
     * @param $status
     * @param $type
     * @return int
     */
    public function add($name, $email, $password, $status, $type)
    {
        $data = [
            's_name'        => $this->dbAdmin()->slashes($name),
            's_email'       => $this->dbAdmin()->slashes($email),
            's_status'      => intval($status),
            's_type'        => intval($type),
            's_ctime'       => time(),
        ];

        if($password){
            $data['s_salt'] = $this->salt(6);
            $data['s_password'] = $this->encryptPwd($password, $data['s_salt']);
        }
        return $this->dbAdmin()->insert($this->table, $data);
    }

    /**
     * 加密密码
     * @param $password
     * @param $salt
     * @return string
     */
    final protected function encryptPwd($password, $salt)
    {
        $str = $password . '_' . $salt;
        return md5('wx.hr.realjf.com_' . $str);
    }

    /**
     * 获取所有正常状态的用户列表
     * @param $ids
     * @return array|bool
     */
    public function getCommNameFromIds($ids)
    {
        $ids = \clsTools::filterIds($ids);
        if(!$ids){
            return FALSE;
        }
        $cond[] = sprintf(" s_status=1 AND s_id IN(%s)", implode(',', $ids));
        $data = $this->dbAdmin()->fetch($this->table, 's_id,s_name', $cond);
        $result = [];
        if($data){
            foreach($data as $v){
                $result[$v['s_id']] = $v['s_name'];
            }
        }
        return $result;
    }

    /**
     * 获取全部用户名
     * @param $ids
     * @return array
     */
    public function getUserNamesByIds($ids)
    {
        $ids = \clsTools::filterIds($ids);
        if(!$ids){
            return [];
        }
        $cond[] = sprintf("s_id IN(%s)", implode(',', $ids));
        $fields = "s_name,s_id,s_state";
        return $this->dbAdmin()->fetch($this->table, $fields, $cond, NULL, 0, 0, 's_id');
    }

    /**
     * 检查邮箱
     * @param $email
     * @param bool|FALSE $id
     * @return bool
     */
    public function checkEmail($email, $id = FALSE)
    {
        $cond = sprintf("s_email='%s'", $this->dbAdmin()->slashes($email));
        $user = $this->dbAdmin()->select($this->table, 's_id', $cond);
        if($user){
            if($id && $user['s_id'] == $id){
                return TRUE;
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 检查密码
     * @param $password
     * @param $id
     * @return bool
     */
    public function checkPwd($password, $id)
    {
        $cond = sprintf("s_id=%d", intval($id));
        $user = $this->dbAdmin()->select($this->table, 's_password,s_salt', $cond);
        if($user['s_password'] == $this->encryptPwd($password, $user['s_salt'])){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 检查用户名
     * @param $name
     * @param bool|FALSE $id
     * @return bool
     */
    public function checkName($name, $id = FALSE)
    {
        $cond = sprintf("s_name='%s'", $this->dbAdmin()->slashes($name));
        $user = $this->dbAdmin()->select($this->table, 's_id', $cond);
        if($user){
            if($id && $user['s_id'] == $id){
                return TRUE;
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 更新最后登陆时间
     * @param $id
     * @param $password
     * @param $salt
     * @return int
     */
    public function updateLoginTime($id, $password, $salt)
    {
        $data = [
            's_last_login_time' => $_SERVER['REQUEST_TIME'],
            's_last_login_ip'   => \clsTools::getIp(true),
        ];
        if($salt && $password){
            $data['s_salt'] = $salt;
            $data['s_password'] = $this->encryptPwd($password, $salt);
        }
        $cond = sprintf("s_id=%d", $id);
        return $this->dbAdmin()->update($this->table, $data, $cond);
    }

    /**
     * @param $id
     * @param $status
     * @return bool|\PDOStatement
     */
    public function setStatus($id, $status)
    {
        if(!$id){
            return false;
        }
        $cond = ['s_id' => intval($id), 's_type!=' . self::TYPE_SUPER];
        $data = [
            's_status' => $status ? intval($status) : 0,
        ];
        return $this->dbAdmin()->update($this->table, $data, $cond);
    }

    /**
     * 产生指定长度A-Za-z0-9的随机字符串
     * @param int $len
     * @return string
     */
    final public function salt($len = 6)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        mt_srand((double)microtime() * 1000000 * getmypid());
        $str = '';
        while(strlen($str)<$len)
            $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $str;
    }

    /**
     * @param $id
     * @param $username
     * @param $email
     * @param $password
     * @return bool|\PDOStatement
     */
    public function edit($id, $username, $email, $password)
    {
        $id = intval($id);
        if(!$id){
            return false;
        }
        $cond = ['s_id' => $id];
        $data = [
            's_name'        => $username,
            's_email'       => $email,
        ];

        if($password){
            $data['s_salt'] = $this->salt(6);
            $data['s_password'] = $this->encryptPwd($password, $data['s_salt']);
        }
        return $this->dbAdmin()->update($this->table, $data, $cond);
    }
}

