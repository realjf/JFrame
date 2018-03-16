<?php

namespace Module\Admin;


class mdlResumeFile extends mdlBase
{
    const TABLE_RESUME_FILE = 'resume_file';

    /**
     * 获取列表
     * @param array $cond
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function getList($cond = [], $page = 1, $limit = 20, $order = 'dateline DESC')
    {
        $data = $this->dbAdmin()->fetchWithPage(self::TABLE_RESUME_FILE, '*', $cond, $order, $limit, $page);
        $new = [];
        if($data['data']){
            foreach($data['data'] as $k => $v){
                $v['dateline'] = $v['dateline'] ? date('Y-m-d H:i:s', $v['dateline']) : '';
                $new[$k] = $v;
            }
            $data['data'] = $new;
        }
        return $data;
    }

    /**
     * @param $uids
     * @param int $job_id
     * @param string $fields
     * @return array
     */
    public function getByUid($uids, $job_id = 0, $fields = 'id,uid,resume_file,size,title')
    {
        if($uids && is_array($uids)){
            $cond = "uid IN(" . implode(',', $uids) . ")";
        }else{
            $cond = "uid = " . intval($uids);
        }
        if($job_id){
            $cond .= " and job_id=" . intval($job_id);
        }
        $data = $this->dbAdmin()->fetch(self::TABLE_RESUME_FILE, $fields, $cond, 'dateline DESC');
        $new = [];
        if($data){
            foreach($data as $v){
                if(isset($v['uid']) && $v['uid']){
                    $new[$v['uid']] = $v;
                }else{
                    $new[] = $v;
                }
            }
        }
        return $new;
    }

    /**
     * 保存
     * @param $id
     * @param $dat
     * @return bool|int
     */
    public function save($id, $dat)
    {
        $data['job_id'] = isset($dat['job_id']) ? intval($dat['job_id']) : 0;
        if(isset($dat['title']) && $dat['title']){
            $data['title'] = \clsTools::removeXss($dat['title']);
        }
        if(isset($dat['resume_file']) && $dat['resume_file']){
            $data['resume_file'] = \clsTools::removeXss($dat['resume_file']);
            $data['size'] = isset($dat['size']) ? floatval($dat['size']) : 0.00;

        }
        if(isset($dat['job_fair_id'])){
            $data['job_fair_id'] =  intval($dat['job_fair_id']);
        }
        if(isset($dat['uid'])){
            $data['uid'] = intval($dat['uid']);
        }
        $data['name'] = isset($dat['name']) ? \clsTools::removeXss($dat['name']) : '';
        $data['telephone'] = isset($dat['telephone']) ? \clsTools::slashes($dat['telephone']) : '';
        $data['school'] = isset($dat['school']) ? \clsTools::removeXss($dat['school']) : '';
        $data['email'] = isset($dat['email']) ? \clsTools::removeXss($dat['email']) : '';
        $data['dateline'] = time();
        if($id){
            $cond = ['id' => intval($id)];
            $res = $this->dbAdmin()->update(self::TABLE_RESUME_FILE, $data, $cond);
            if($res !== FALSE){
                return TRUE;
            }
            return FALSE;
        }else{
            return $this->dbAdmin()->insert(self::TABLE_RESUME_FILE, $data);
        }
    }

    /**
     * 根据Id获取详情
     * @param $id
     * @return array|bool
     */
    public function get($id)
    {
        if(!$id){
            return FALSE;
        }
        $cond = ['id' => intval($id)];
        $data = $this->dbAdmin()->select(self::TABLE_RESUME_FILE, '*', $cond);
        if($data){
            $data['dateline'] = date('Y-m-d H:i:s', $data['dateline']);
        }
        return $data;
    }

    /**
     * 根据id删除记录
     * @param $id
     * @return bool|int
     */
    public function delById($id)
    {
        if(!$id){
            return FALSE;
        }
        $cond = ['id' => intval($id)];
        $res = $this->dbAdmin()->select(self::TABLE_RESUME_FILE, 'id', $cond);
        if(!$res){
            return FALSE;
        }
        return $this->dbAdmin()->delete(self::TABLE_RESUME_FILE, $cond);
    }
}