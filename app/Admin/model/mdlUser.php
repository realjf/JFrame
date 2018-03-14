<?php


namespace Module\Admin;


class mdlUser extends mdlBase
{
    private $_tbl_user = "wx_member_user";

    const STATUS_NORMAL = 0;
    const STATUS_ABNORMAL = 1;

    public static $_status = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_ABNORMAL => '不正常',
    ];

    public function getList($cond = [], $page = 1, $limit = 20)
    {
        $data = $this->dbAdmin()->fetchWithPage($this->_tbl_user, "*", $cond, "uid desc", $limit, $page);
        if($data['data']){
            foreach ($data['data'] as $k => $v) {
                $data['data'][$k]['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                $data['data'][$k]['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
                $data['data'][$k]['nick'] = base64_decode($v['nick']);
                $data['data'][$k]['ip_str'] = $v['ip'] ? long2ip($v['ip']) : '';
            }
        }
        return $data;
    }

    /**
     * 删除指定用户
     * @param $uid
     * @return int
     */
    public function delById($uid) {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $cond = " uid = ".$uid;
        return $this->dbAdmin()->delete($this->_tbl_user, $cond);
    }

    /**
     * 获取单个用户信息
     * @param $uid
     * @param string $field
     * @return array|bool
     */
    public function getUserInfoByUid($uid, $field = '')
    {
        if(!$uid){
            return [];
        }
        $uid = intval($uid);
        $field = $field ? $field : '*';
        $cond = " uid = ".$uid;
        $data = $this->dbAdmin()->select($this->_tbl_user, $field, $cond);
        $data['nick'] = base64_decode($data['nick']);
        return $data;
    }

    /**
     * 获取用户信息
     * @param $uids
     * @param string $field
     * @return array
     */
    public function getUserInfoByUids($uids, $field = '*')
    {
        if(!$uids){
            return [];
        }
        $uids = is_array($uids) ? implode(',', array_unique($uids)) : intval($uids);
        $cond = " uid IN (" .$uids .")";
        $res = $this->dbAdmin()->fetch($this->_tbl_user, $field, $cond);
        $data = [];
        foreach($res as $k => $v){
            $data[$v['uid']]['address'] = $v['address'];
            $data[$v['uid']]['name'] = $v['name'];
            $data[$v['uid']]['nick'] = base64_decode($v['nick']);
            $data[$v['uid']]['age'] = $v['age'];
        }
        return $data;
    }

    /**
     * 设置用户状态
     * @param $uid
     * @param $status
     * @return int
     */
    public function setStatus($uid, $status)
    {
        $uid = intval($uid);
        $data['isdead'] = $status ? 1 : 0;
        $cond = " uid = ".$uid;
        return $this->dbAdmin()->update($this->_tbl_user, $data, $cond);
    }
}