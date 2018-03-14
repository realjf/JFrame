<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/5
 * Time: 12:47
 */

namespace Module\Admin;


class mdlJob extends mdlBase
{
    /**
     * 岗位信息表
     */
    const TABLE_JOB_INFO  = "job_info";

    /**
     * 发布状态
     */
    const PUB_YES = 2;
    const PUB_NO = 1;

    /**
     * 工作信息状态
     */
    const STATUS_TRASH = 2;
    const STATUS_NORMAL = 1;

    /**
     * 校招状态
     */
    const SCHOOL_RECRUIT_YES = 1;
    const SCHOOL_RECRUIT_NO = 0;

    /**
     * 招聘岗位类别
     * @var array
     */
    public  static $_category = [
        1 => '校园招聘',
        2 => '技术研发类',
        3 => '产品策划类',
        4 => '运营编辑类',
        5 => '美术设计类',
        6 => '职能管理类',
        0 => '所有岗位',
    ];

    /**
     * @param $cond
     * @param int $page
     * @param int $limit
     * @return array|bool
     */
    public function getList($cond, $page = 1, $limit = 20)
    {
        $data = $this->dbAdmin()->fetchWithPage(self::TABLE_JOB_INFO, '*', $cond, 'j_id desc', $limit, $page);
        if($data['data']){
            $data['data'] = $this->fieldPrefixRm($data['data'], 'j_');
            foreach ($data['data'] as $k => $v){
                $v['dateline'] = $v['dateline'] ? date('Y-m-d H:i:s', $v['dateline']) : '';
                $v['category'] = $v['category'] ? self::$_category[$v['category']] : '';
                $data['data'][$k] = $v;
            }
        }
        return $data;
    }

    /**
     * @param $cond
     * @return array|bool
     */
    public function fetchByCond($cond)
    {
        $data = $this->dbAdmin()->fetch(self::TABLE_JOB_INFO, '*', $cond, 'j_id desc');
        if($data){
            $data = $this->fieldPrefixRm($data, 'j_');
            foreach ($data as $k => $v){
                $v['dateline'] = $v['dateline'] ? date('Y-m-d H:i:s', $v['dateline']) : '';
                $v['category_id'] = $v['category'];
                $v['category'] = $v['category'] ? self::$_category[$v['category']] : '';
                $data[$k] = $v;
            }
        }
        return $data;
    }

    /**
     * 获取某类别下的所有岗位信息
     * @param $cid
     * @param string $place
     * @param string $field
     * @param string $limit
     * @param int $page
     * @return array
     */
    public function getJobListByCid($cid, $place = "", $field ="*", $limit = "", $page = 0) {
        $cid = intval($cid);
        $cid = array_key_exists($cid, self::$_category) ? $cid : 0;
        $page = max(1, intval($page)) == 1 ? 0 : intval($page-1);
        $limit = intval($limit);
        $cond = "";
        if ($cid != 0) {
            $cond = " j_category = ".$cid;
        }
        if (!empty($place)) {
            $cond = " j_place LIKE '%".$place."%'";
        }
        $orderBy = "j_id DESC";
        $start = ($limit * $page);
        $data = $this->dbAdmin()->fetch(self::TABLE_JOB_INFO, $field, $cond, $orderBy, $limit, $start);
        if($data){
            $data = $this->fieldPrefixRm($data, 'j_');
            foreach ($data as $k => $v) {
                $data[$k]['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                $data[$k]['category'] =  self::$_category[$v['category']];
                $data[$k]['duty'] = \clsTools::splitLine($v['duty']);
            }
        }

        return $data;
    }

    /**
     * 根据id获取岗位具体信息
     * @param $id
     * @return array|bool
     */
    public function getJobDetailById($id) {
        if(!$id){
            return [];
        }
        $id = intval($id);
        $field = "*";
        $cond = " j_id = ".$id;
        $data = $this->dbAdmin()->select(self::TABLE_JOB_INFO, $field, $cond);
        $data = $data ? $this->fieldPrefixRm($data, 'j_') : $data;
        $data['dateline'] = date('Y-m-d', $data['dateline']);
        return $data;
    }

    /**
     * 添加招聘岗位信息
     * @param $data
     * @return int
     */
    public function add($data) {
        $dat['j_name'] = $data['job_name'];
        $dat['j_category'] = intval($data['category']);
        $dat['j_number'] = intval($data['number']);
        $dat['j_school'] = intval($data['isschool']);
        $dat['j_duty'] = isset($data['duty']) ? $data['duty'] : '';
        $dat['j_place'] = $data['place'];
        $dat['j_require'] = isset($data['require']) ? $data['require'] : '';
        $dat['j_dateline'] = time();
        $onDuplicate = " UPDATE j_name='".$dat['j_name']."',j_category=".$dat['j_category'].",j_place='".$dat['j_place']."',j_duty='".$dat['j_duty'].
            "',j_require='".$dat['j_require']."',j_number=".$dat['j_number'].",j_school=".$dat['j_school'];
        return $this->dbAdmin()->insert(self::TABLE_JOB_INFO, $dat, $onDuplicate);
    }

    /**
     * 更新招聘信息
     * @param $id
     * @param $data
     * @return int
     */
    public function updateJobById($id, $data)
    {
        if(!$id){
            return FALSE;
        }
        $cond = "j_id = " . intval($id);
        $dat['j_name'] = $data['j_name'];
        $dat['j_category'] = intval($data['j_category']);
        $dat['j_number'] = intval($data['j_number']);
        $dat['j_school'] = intval($data['j_school']);
        $dat['j_duty'] = $data['j_duty'];
        $dat['j_place'] = $data['j_place'];
        $dat['j_require'] = $data['j_require'];
        $dat['j_dateline'] = time();
        $dat['j_follow'] = intval($data['j_follow']); // 跟进人id
        return $this->dbAdmin()->update(self::TABLE_JOB_INFO, $dat, $cond);
    }

    /**
     * 返回岗位id和岗位名称
     * @param $ids
     * @return array
     */
    public function getJobNameById($ids) {
        if(!$ids){
            return FALSE;
        }
        $ids = is_array($ids) ? implode(',', $ids) : intval($ids);
        $cond = " j_id IN (".$ids.")";
        $field = "j_id, j_name";
        $result = $this->dbAdmin()->fetch(self::TABLE_JOB_INFO, $field, $cond);
        $data = array();
        $result = $this->fieldPrefixRm($result, 'j_');
        foreach ($result as $k => $v) {
            $data[$v['id']] = $v['name'];
        }
        return $data;
    }

    /**
     * 删除指定的工作信息
     * @param $id
     * @return int
     */
    public function delJobById($id){
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $cond = " j_id = ".$id;
        return $this->dbAdmin()->delete(self::TABLE_JOB_INFO, $cond);
    }

    /**
     * 设置发布状态
     * @param $id
     * @param $status
     * @return int
     */
    public function setPubStatus($id, $status){
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $status = intval($status)==2 ? self::PUB_YES : self::PUB_NO;
        $cond = "j_id = ".$id;
        $data = [
            "j_pub_status" => $status,
            "j_dateline" => time()
        ];
        return $this->dbAdmin()->update(self::TABLE_JOB_INFO, $data, $cond);
    }

    /**
     * 设置校招状态
     */
    public function setSchoolStatus($id, $status){
        if(!$id){
            return FALSE;
        }
        $id = intval($id);
        $status = intval($status)==1 ? self::SCHOOL_RECRUIT_YES : self::SCHOOL_RECRUIT_NO;
        $cond = "j_id = ".$id;
        $data = [
            "j_school" => $status,
        ];
        return $this->dbAdmin()->update(self::TABLE_JOB_INFO, $data, $cond);
    }
}