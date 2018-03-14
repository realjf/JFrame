<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/6
 * Time: 15:11
 */

namespace Module\Admin;


class mdlResumeOp extends mdlBase
{
    /**
     * 用户简历信息表
     */
    const TABLE_RESUME_LIBRARY = "resume_library";
    /**
     * 用户简历扩展表
     */
    const TABLE_RESUME_LIBRARY_EXT = "resume_library_ext";
    /**
     * 简历投递快照表
     */
    const TABLE_RESUME_SNAPSHOT = "resume_snapshot";
    /**
     * 简历快照扩展表
     */
    const TABLE_RESUME_SNAPSHOT_EXT = "resume_snapshot_ext";
    /**
     * 简历投递记录表
     */
    const TABLE_RESUME_DELIVER_LOG = "resume_deliver_log";

    /**
     * 获取用户最新投递记录
     * @param array $cond
     * @param int|string $limit
     * @param int $page
     * @return array
     */
    public function getDeliverLogList($cond = [], $page = 1, $limit = 20)
    {
        $field = "*";
        $condString = $this->dbAdmin()->mkCond($cond);
        $countSqlTbl = sprintf("(select distinct job_id,uid,status from %s %s ) as b " , self::TABLE_RESUME_DELIVER_LOG, $condString);
        $count = $this->dbAdmin()->count($countSqlTbl, 1);
        $cond[] = "1 GROUP BY job_id,uid,status";
        $orderBy = "dateline DESC";
        $data = \clsTools::multi($count, $limit, $page);
        $start = $page * $limit - $limit;
        $data['data'] = $this->dbAdmin()->fetch(self::TABLE_RESUME_DELIVER_LOG, $field, $cond, $orderBy, $limit, $start);
        return $data;
    }

    /**
     * 获取用户的投递信息
     * @param $uid
     * @return array|bool
     */
    public function getDeliverLogByUid($uid)
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $cond = "uid = " . $uid;
        $field = "*";
        $data = $this->dbAdmin()->fetch(self::TABLE_RESUME_DELIVER_LOG, $field, $cond);
        foreach($data as $k => $v){
            $data[$k]['dateline'] = date('Y-m-d', $v['dateline']);
        }
        return $data;
    }

    /**
     * 根据
     * @param $id
     * @return array|bool
     */
    public function getDeliverLogById($id)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = "id = " . $id;
        $field = "*";
        $data = $this->dbAdmin()->select(self::TABLE_RESUME_DELIVER_LOG, $field, $cond);
        $data['dateline'] = date('Y-m-d', $data['dateline']);
        return $data;
    }

    /**
     * 组装信息
     * @param $data
     * @return array
     */
    public function mergeData($data)
    {
        $ext = [];
        if($data){
            foreach($data as $key => $val){
                $ext[$val['job_id']] = mdlJob::instance()->getJobDetailById($val['job_id']);
            }
        }
        return $ext;
    }

    /**
     * 获取所有用户简历
     * @param $uids
     * @return array
     */
    public function getResumeByUids($uids)
    {
        if(!$uids){
            return [];
        }
        $uids = is_array($uids) ? implode(',', array_unique($uids)) : intval($uids);

        $cond = " uid IN (" . $uids . ")";
        $field = "*";
        $result = $this->dbAdmin()->fetch(self::TABLE_RESUME_SNAPSHOT, $field, $cond);
        $data = [];
        if($result){
            foreach($result as $k => $v){
                $data[$v['uid']]['sex'] = \defConst::$_sex[$v['sex']];
                $data[$v['uid']]['name'] = $v['name'];
                $data[$v['uid']]['degree'] = \defConst::$_degree[$v['degree']];
                $data[$v['uid']]['email'] = $v['email'];
                $data[$v['uid']]['phone'] = $v['mobile_phone'];
                $data[$v['uid']]['qq'] = $v['qq'];
                $data[$v['uid']]['exp'] = $v['experience'];
                $data[$v['uid']]['edu'] = $v['edu_backgnd'];
                $data[$v['uid']]['work_life'] = $v['graduation_date'] ? date('Y', time()) - date('Y', $v['graduation_date']) : 0;
                $data[$v['uid']]['work_life'] = intval($data[$v['uid']]['work_life']) > 0 ? $data[$v['uid']]['work_life'] : 0;
                $data[$v['uid']]['major'] = $v['major'];
                $data[$v['uid']]['school'] = $v['school'];
                $data[$v['uid']]['graduation_date'] = date('Y-m', $v['graduation_date']);
                if($v['edu_backgnd']){  // 获取最新毕业院校和工作年限
                    $edu = json_decode($v['edu_backgnd'], true);
                    $graduateTime = $count = 0;
                    if($edu){
                        foreach($edu as &$d){
                            if($count == 0){
                                $graduateTime = strtotime($d['graduation'] . "-01");
                                $data[$v['uid']]['graduate_from'] = $d['school_name'];
                            } elseif($graduateTime < strtotime($d['graduation'])){
                                $graduateTime = strtotime($d['graduation'] . "-01");
                                $data[$v['uid']]['graduate_from'] = $d['school_name'];
                            }
                            $worklife = $graduateTime ? date('Y', time()) - date('Y', $graduateTime) : 0;
                            $data[$v['uid']]['work_life'] = $worklife > 0 ? $worklife : 0;
                            $count++;
                        }
                    }else{
                        $data[$v['uid']]['graduate_from'] = "";
                    }
                } else{
                    $data[$v['uid']]['graduate_from'] = "";
                }
                $data[$v['uid']]['graduate_from'] = $v['school'] ?: $data[$v['uid']]['graduate_from'];
            }

            $uids2 = array_keys($data);
            $rest = array_diff(explode(',', $uids), $uids2);
            foreach($rest as $v){
                $data[$v]['work_life'] = "0";
                $data[$v]['graduate_from'] = "";
            }
        }

        return $data;
    }

    /**
     * 获取简历快照
     * @param $id
     * @return array
     */
    public function getResumeById($id)
    {
        if(!$id){
            return [];
        }
        $cond = " id =" . intval($id);
        $field = "*";
        $data = $this->dbAdmin()->select(self::TABLE_RESUME_SNAPSHOT, $field, $cond);
        if($data){
            if($data['experience']){
                $data['experience'] = json_decode($data['experience'], TRUE);
            }
            if($data['edu_backgnd']){
                $data['edu_backgnd'] = json_decode($data['edu_backgnd'], TRUE);
            }
        }
        return $data;
    }

    /**
     * 判断用户是否投递过相同岗位的简历,返回相应简历id
     * @param $uid
     * @param $job_id
     * @return array|bool
     */
    public function isSameInSnapshot($uid, $job_id)
    {
        if(!$uid || !$job_id){
            return [];
        }
        $uid = intval($uid);
        $field = "resume_id";
        $cond = "uid = " . $uid . " AND job_id = '" . $job_id . "'";
        $result = $this->dbAdmin()->select(self::TABLE_RESUME_DELIVER_LOG, $field, $cond);

        $result2 = array();
        if(!empty($result)){
            $result2 = $this->dbAdmin()->select(self::TABLE_RESUME_SNAPSHOT, "id", " id = " . $result['resume_id']);
        }
        return $result2;
    }

    /**
     * 判断用户是否存在简历，并返回简历id
     * @param $uid
     * @param $table
     * @return array|bool
     */
    public function isExistRecordByUid($uid, $table = self::TABLE_RESUME_LIBRARY)
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $field = "id";
        $cond = "uid = " . $uid;
        return $this->dbAdmin()->select($table, $field, $cond);
    }

    /**
     * 更新简历投递记录
     * @param $id
     * @param $dat
     * @return int
     */
    public function updateDeliverLogById($id, $dat)
    {
        if(!$id){
            return FALSE;
        }
        $data = [];
        $cond = "id =" . intval($id);
        $data['status'] = intval($dat['status']);
        $data['remarks'] = $dat['remarks'];

        return $this->dbAdmin()->update(self::TABLE_RESUME_DELIVER_LOG, $data, $cond);
    }

    /**
     * 获取公共简历库简历
     * @param $id
     * @return array|bool
     */
    public function getByResumeId($id)
    {
        if(!$id){
            return FALSE;
        }
        $cond = " id =" . intval($id);
        $field = "*";
        $data = $this->dbAdmin()->select(self::TABLE_RESUME_SNAPSHOT, $field, $cond);
        if($data){
            if($data['experience']){
                $data['experience'] = json_decode($data['experience'], TRUE);
            }
            if($data['edu_backgnd']){
                $data['edu_backgnd'] = json_decode($data['edu_backgnd'], TRUE);
            }
        }
        return $data;
    }

    /**
     * 获取用户投递简历的审查进度
     * @param $uid
     * @return array|bool
     */
    public function getResumeStatus($uid)
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $field = "status, resume_id";
        $cond = "uid = " . $uid;
        return $this->dbAdmin()->fetch(self::TABLE_RESUME_DELIVER_LOG, $field, $cond);
    }

    /**
     * 更新用户简历的审查进度
     * @param $id
     * @param $status
     * @return int
     */
    public function setResumeStatus($id, $status)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = "id = " . $id;
        $data['status'] = intval($status);
        return $this->dbAdmin()->update(self::TABLE_RESUME_DELIVER_LOG, $data, $cond);
    }

    /**
     * 更新简历快照表
     * @param $data
     * @param $rid
     * @return mixed
     */
    public function dumpToSnapshot($data, $rid = 0)
    {
        if(!$data['uid']){
            return FALSE;
        }
        $cond = "uid = " . $data['uid'];
        $data['uid'] = intval($data['uid']);
        $data['dateline'] = time();
        if(empty($rid)){
            return $this->dbAdmin()->insert(self::TABLE_RESUME_SNAPSHOT, $data);
        } else{
            $cond .= " AND id = " . intval($rid);
            return $this->dbAdmin()->update(self::TABLE_RESUME_SNAPSHOT, $data, $cond);
        }
    }

    /**
     * @param $id
     * @return int
     */
    public function delResumeSnapshotById($id)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = "id = " . $id;
        return $this->dbAdmin()->delete(self::TABLE_RESUME_SNAPSHOT, $cond);
    }

    /**
     * @param $id
     * @return int
     */
    public function delResumeSnapshotExtByRid($id)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = "resume_id = " . $id;
        return $this->dbAdmin()->delete(self::TABLE_RESUME_SNAPSHOT_EXT, $cond);
    }

    /**
     * 根据用户id删除简历
     * @param $uid
     * @return bool|int
     */
    public function delResumeByUid($uid)
    {
        if(!$uid){
            return FALSE;
        }
        $uid = intval($uid);
        $cond['uid'] = $uid;
        $rid = $this->dbAdmin()->select(self::TABLE_RESUME_LIBRARY, '*', $cond);
        if($rid){
            $con['resume_id'] = $rid['id'];
            $res = $this->dbAdmin()->select(self::TABLE_RESUME_LIBRARY_EXT, '*', $con);
            if($res){
                $this->dbAdmin()->delete(self::TABLE_RESUME_LIBRARY_EXT, $con, 1);
            }
            return $this->dbAdmin()->delete(self::TABLE_RESUME_LIBRARY, $cond, 1);
        }
        return FALSE;
    }

    /**
     * @param $uid
     * @param $jobId
     * @return int
     */
    public function delDeliverLog($uid, $jobId)
    {
        if(!$uid || !$jobId){
            return FALSE;
        }
        $uid = intval($uid);
        $jobId = intval($jobId);
        $cond = "uid = " . $uid . " AND job_id =" . $jobId;
        return $this->dbAdmin()->delete(self::TABLE_RESUME_DELIVER_LOG, $cond);
    }

    /**
     * 根据简历投递记录id删除
     * @param $id
     * @return int
     */
    public function delDeliverLogById($id)
    {
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = "id = " . $id;
        return $this->dbAdmin()->delete(self::TABLE_RESUME_DELIVER_LOG, $cond);
    }

    /**
     * 返回简历库（简历快照）列表
     * @param array $cond
     * @param int $page
     * @param int $limit
     * @return array|bool
     */
    public function getResumeList($cond = [], $page = 1, $limit = 20)
    {
        $field = "*";
        $orderBy = " id DESC";
        $data = $this->dbAdmin()->fetchWithPage(self::TABLE_RESUME_SNAPSHOT, $field, $cond, $orderBy, $limit, $page);
        if($data['data']){
            foreach($data['data'] as &$v){
                $exp = new mdlJson($v['experience']);
                $edu = new mdlJson($v['edu_backgnd']);
                $v['experience'] = $exp->jsonArr;
                $v['edu_backgnd'] = $edu->jsonArr;
            }
        }
        return $data;
    }
}