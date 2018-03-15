<?php
/**
 * web简历表操作
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/9
 * Time: 16:50
 */

namespace Module\Admin;


class mdlResumeWebOp extends mdlBase
{
    /**
     * 用户简历
     * @var string
     */
    private static $_tb_resume_lib = 'resume_library';
    /**
     * 用户简历扩展表
     * @var string
     */
    private static $_tb_resume_lib_ext = 'resume_library_ext';
    /**
     * 简历快照表
     * @var string
     */
    private static $_tb_snapshot = 'resume_snapshot';
    /**
     * 简历快照扩展表
     * @var string
     */
    private static $_tb_snapshot_ext = 'resume_snapshot_ext';
    /**
     * 简历投递记录
     * @var string
     */
    private static $_tb_delivery_log = 'resume_deliver_log';


    /**
     * 获取简历
     * @param $resumeId
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function getResume($resumeId, $uid, $fields = '*')
    {
        $resumeId = intval($resumeId);
        $uid = intval($uid);
        $cond = "id = ".$resumeId . " AND uid=" . $uid;
        return $this->dbAdmin()->select(self::$_tb_resume_lib, $fields, $cond);
    }

    /**
     * 更新简历扩展表
     * @param $resumeId
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function getResumeExt($resumeId, $uid, $fields = '*')
    {
        $resumeId = intval($resumeId);
        $uid = intval($uid);
        $cond = "resume_id = ".$resumeId;
        $res = $this->dbAdmin()->select(self::$_tb_resume_lib, $fields, ['id'=>$resumeId, 'uid'=>$uid]);
        if($res){
            return $this->dbAdmin()->select(self::$_tb_resume_lib_ext, $fields, $cond);
        }else{
            return FALSE;
        }
    }

    /**
     * 获取简历快照信息
     * @param $resumeId
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function getSnapshot($resumeId, $uid, $fields = '*')
    {
        $resumeId = intval($resumeId);
        $uid = intval($uid);
        $cond = "id = ".$resumeId . " AND uid=" . $uid;
        return $this->dbAdmin()->select(self::$_tb_snapshot, $fields, $cond);
    }

    /**
     * 获取简历快照扩展信息
     * @param $resumeId
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function getSnapshotExt($resumeId, $uid, $fields = '*')
    {
        $resumeId = intval($resumeId);
        $uid = intval($uid);
        $cond = "resume_id = " . $resumeId;
        $res = $this->dbAdmin()->select(self::$_tb_snapshot, $fields, ['id'=>$resumeId, 'uid'=>$uid]);
        if($res){
            return $this->dbAdmin()->select(self::$_tb_snapshot_ext, $fields, $cond);
        }else{
            return FALSE;
        }
    }

    /**
     * 获取简历投递记录
     * @param $resumeId
     * @param $uid
     * @param string $fields
     * @return array|bool
     */
    public function getDeliverLog($resumeId, $uid, $fields = '*')
    {
        $resumeId = intval($resumeId);
        $uid = intval($uid);
        $cond = "resume_id = " . $resumeId . " AND uid=" . $uid;
        return $this->dbAdmin()->select(self::$_tb_delivery_log, $fields, $cond);
    }

    /**
     * 更新简历快照
     * @param $resumeId
     * @param $uid
     * @param $data
     * @return int
     */
    public function updateSnapshot($resumeId, $uid, $data)
    {
        $cond = "id=" . intval($resumeId) . " AND uid=" . intval($uid);
        $uData = [];
        if(isset($data['name']) && $data['name']){
            $uData['name'] = \clsTools::removeXss(trim($data['name']));
        }
        if(isset($data['sex']) && $data['sex']){
            $uData['sex'] = intval($data['sex']);
        }
        if(isset($data['phone']) && $data['phone']){
            $uData['mobile_phone'] = \clsTools::removeXss(trim($data['phone']));
        }
        if(isset($data['email']) && $data['email']){
            $uData['email'] = \clsTools::removeXss(trim($data['email']));
        }
        if(isset($data['qq']) && $data['qq']){
            $uData['qq'] = \clsTools::removeXss(trim($data['qq']));
        }
        if(isset($data['degree']) && $data['degree']){
            $uData['degree'] = intval($data['degree']);
        }
        if(isset($data['major']) && $data['major']){
            $uData['major'] = \clsTools::removeXss(trim($data['major']));
        }
        if(isset($data['graduation']) && $data['graduation']){
            $uData['graduation_date'] = strtotime($data['graduation']);
        }
        $uData['dateline'] = time();
        $res = $this->dbAdmin()->select(self::$_tb_snapshot, '*', $cond);
        if($res){
            return $this->dbAdmin()->update(self::$_tb_snapshot, $uData, $cond);
        }else{
            return FALSE;
        }
    }

    /**
     * 更新简历快照扩展表
     * @param $resumeId
     * @param $uid
     * @param $data
     * @return int
     */
    public function updateSnapshotExt($resumeId, $uid, $data)
    {
        $cond = "resume_id=" . intval($resumeId);
        $uData = [];
        if(isset($data['job_id']) && $data['job_id']){
            $uData['job_id'] = intval($data['job_id']);
            mdlResumeOp::instance()->updateDeliverLogByRid($resumeId, $uid, $data['job_id']);
        }
        if(isset($data['salary']) && $data['salary']){
            $uData['expected_salary'] = intval($data['salary']);
        }
        if(isset($data['arrival']) && $data['arrival']){
            $uData['arrival_date'] = strtotime($data['arrival']);
        }
        if(isset($data['promote']) && $data['promote']){
            $uData['recruitment_channel'] = intval($data['promote']);
        }
        if(isset($data['idcard']) && $data['idcard']){
            $uData['id_number'] = \clsTools::removeXss(trim($data['idcard']));
        }
        if(isset($data['birthday']) && $data['birthday']){
            $uData['birthdate'] = strtotime($data['birthday']);
        }
        if(isset($data['marriage']) && $data['marriage']){
            $uData['marital_status'] = intval($data['marriage']);
        }
        if(isset($data['major']) && $data['major']){
            $uData['major'] = \clsTools::removeXss(trim($data['major']));
        }
        if(isset($data['place_birth']) && $data['place_birth']){
            $uData['place_birth'] = \clsTools::removeXss($data['place_birth']);
        }
        if(isset($data['register_place']) && $data['register_place']){
            $uData['register_place'] = \clsTools::removeXss($data['register_place']);
        }
        if(isset($data['current_addr']) && $data['current_addr']){
            $uData['curr_address'] = \clsTools::removeXss($data['current_addr']);
        }
        if(isset($data['contactPerson']) && $data['contactPerson']){
            $uData['emerg_contact_person'] = \clsTools::removeXss(trim($data['contactPerson']));
        }
        if(isset($data['contactPhone']) && $data['contactPhone']){
            $uData['emerg_contact_phone'] = \clsTools::removeXss(trim($data['contactPhone']));
        }
        if(isset($data['hobby']) && $data['hobby']){
            $uData['hobby'] = str_replace('/[\s]+/i', '', \clsTools::removeXss(trim($data['hobby'])));
        }
        if(isset($data['referees']) && $data['referees']){
            $uData['referees'] = \clsTools::removeXss(trim($data['referees']));
        }
        $uData['dateline'] = time();

        $ret = $this->dbAdmin()->select(self::$_tb_resume_lib, '*', ['uid'=> intval($uid)]);
        $ret1 = $ret ? $this->dbAdmin()->select(self::$_tb_resume_lib_ext, '*', ['resume_id'=>intval($ret['id'])]) : FALSE;
        $libData = $uData;
        if($ret && $ret1){
            $this->dbAdmin()->update(self::$_tb_resume_lib_ext, $libData, ['resume_id'=>intval($ret['id'])]);
        }else if($ret && !$ret1){
            $libData['resume_id'] = intval($ret['id']);
            $this->dbAdmin()->insert(self::$_tb_resume_lib_ext, $libData);
        }
        $res = $this->dbAdmin()->select(self::$_tb_snapshot, '*', ['uid'=> intval($uid), 'id'=> intval($resumeId)]);
        $res1 = $res ? $this->dbAdmin()->select(self::$_tb_snapshot_ext, '*', ['resume_id'=>intval($resumeId)]) : FALSE;
        if($res && $res1){
            return $this->dbAdmin()->update(self::$_tb_snapshot_ext, $uData, $cond);
        }else if($res && !$res1){
            $uData['resume_id'] = intval($resumeId);
            return $this->dbAdmin()->insert(self::$_tb_snapshot_ext, $uData);
        }else{
            return FALSE;
        }
    }
}