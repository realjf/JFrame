<?php


namespace Module\Admin;


class mdlAdmin extends mdlBase
{
    /**
     * 管理员表
     */
    const TABLE_ADMIN = "sys_member_admin";

    public static $_status = [
        0 => "可用",
        1 => "不可用"
    ];


    /**
     * 用户身份验证
     * @param $username
     * @param $password
     * @return bool
     */
    public function login_sign($username, $password)
    {
        if(!$username || !$password){
            return FALSE;
        }
        $field = "id, username, email, isdead, isSuper";
        $cond["username"] = $username;
        $cond["password"] = md5($password);
        return $this->dbAdmin()->select(self::TABLE_ADMIN, $field, $cond);
    }


    /**
     * 获取所有管理员用户信息
     * @return array
     */
    public function show_admins()
    {
        $field = "id, username, email, isdead, isSuper, dateline";
        $cond = "";
        $orderBy = "id ASC";
        $data = $this->dbAdmin()->fetch(self::TABLE_ADMIN, $field, $cond, $orderBy);
        foreach($data as &$v){
            $v['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
        }
        return $data;
    }

    /**
     * 获取管理员用户的基本信息
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function get_admin($uid, $fields = '')
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $fields = $fields ? 'username,email' : $fields;
        $cond = "id = " . $uid;
        $data = $this->dbAdmin()->select(self::TABLE_ADMIN, $fields, $cond);
        $new['id'] = $data['id'];
        $new['password'] = "";
        $new['email'] = $data['email'];
        $new['username'] = $data['username'];
        $new['isSuper'] = $data['isSuper'];
        $new['isdead'] = $data['isdead'];
        return $new;
    }

    /**
     * 验证管理员身份
     * @param $username
     * @param $password
     * @return array|bool
     */
    public function validate_admin($username, $password)
    {
        IF(!$password || !$username){
            return FALSE;
        }
        $cond['username'] = $username;
        $cond['password'] = md5($password);
        $res = $this->dbAdmin()->select(self::TABLE_ADMIN, '*', $cond);
        return $res;
    }

    /**
     * 添加新用户
     * @param $data
     * @return int
     */
    public function add_admin($data)
    {
        if(!$data['username'] || !$data['password'] || !$data['email']){
            return FALSE;
        }
        $dat['username'] = $data['username'];
        $dat['email'] = $data['email'];
        $dat['password'] = md5($data['password']);
        $dat['dateline'] = time();
        $dat['isdead'] = 0;
        $dat['isSuper'] = 0;
        return $this->dbAdmin()->insert(self::TABLE_ADMIN, $dat);
    }

    /**
     * 删除用户
     * @param $id
     * @return int
     */
    public function del_admin($id)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = " id = " . $id;
        return $this->dbAdmin()->delete(self::TABLE_ADMIN, $cond);
    }

    /**
     * 修改用户信息
     * @param $data
     * @param $uid
     * @return int
     */
    public function update_admin($uid, $data)
    {
        if(!$uid || (isset($data['password']) && !$data['password'])){
            return FALSE;
        }
        $uid = intval($uid);
        $cond = " id = " . $uid;
        $dat['username'] = isset($data['username']) && $data['username'] ? $data['username'] : "";
        $dat['email'] = isset($data['email']) && $data['email'] ? $data['email'] : "";
        $dat['password'] = md5($data['password']);
        $dat['isdead'] = isset($data['isdead']) && $data['isdead'] ? 1 : 0;
        $dat['dateline'] = time();
        return $this->dbAdmin()->update(self::TABLE_ADMIN, $dat, $cond);
    }

    /**
     * 更新登陆时间
     * @param $uid
     * @return bool|int
     */
    public function update_login_date($uid)
    {
        if(!$uid){
            return FALSE;
        }
        $cond['id'] = intval($uid);
        $data['dateline'] = time();
        return $this->dbAdmin()->update(self::TABLE_ADMIN, $data, $cond);
    }

    /**
     * 设置用户状态
     * @param $uid
     * @param $status
     * @return int
     */
    public function setStatus($uid, $status = 0)
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $old = self::get_admin($uid, '*');
        if($old['isSuper']){
            return FALSE;
        }
        $data['isdead'] = intval($status);
        $cond = " id = " . $uid;
        $dat = $this->dbAdmin()->update(self::TABLE_ADMIN, $data, $cond);
        return $dat;
    }
}