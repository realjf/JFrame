<?php


namespace Module\Admin;


class mdlJobFair extends mdlBase
{
    const TABLE_JOB_FAIR = "job_fair";

    const TYPE_SOCIAL_RECRUIT = 0; // 社会招聘会
    const TYPE_SCHOOL_RECRUIT = 1; // 校园招聘会

    public static $_types = [
        self::TYPE_SCHOOL_RECRUIT => '校园招聘会',
        self::TYPE_SOCIAL_RECRUIT => '社会招聘会',
    ];

    const STATUS_SHOW = 1;
    const STATUS_HIDE = 0;

    /**
     * 获取列表数据
     * @param int $type
     * @param int $page
     * @param int $limit
     * @param string $field
     * @param string $order
     * @param bool|TRUE $format
     * @return array
     */
    public function getList($type = 1, $page = 1, $limit = 20,  $field = '*', $order = 'sort DESC', $format = TRUE)
    {
        $page = intval($page);
        $limit = intval($limit);
        $cond = "";
        if(intval($type) == self::TYPE_SCHOOL_RECRUIT){
            $cond = "`type` =" . self::TYPE_SCHOOL_RECRUIT;
        }
        $start = intval(($page - 1) * $limit);
        $data = $this->dbAdmin()->fetchWithPage(self::TABLE_JOB_FAIR, $field, $cond, $order, $limit, $start);
        if($format && $data['data']){
            $data['data'] = self::_formatList($data['data']);
        }
        return $data ?: [];
    }

    /**
     * 格式化数据
     * @param $data
     * @return array
     */
    private function _formatList($data)
    {
        $new = [];
        if($data){
            foreach($data as $v){
                $new[$v['id']] = [
                    'id'        => $v['id'],
                    'title'     => $v['title'],
                    'url'       => $v['url'],
                    'pic_url'   => $v['pic_url'],
                    'stime'     => $v['stime'] ? date('Y-m-d H:i', $v['stime']) : '待定',
                    'etime'     => $v['etime'] ? date('Y-m-d H:i', $v['etime']) : '待定',
                    'sort'      => $v['sort'],
                    'desc'      => $v['desc'],
                    'place'     => $v['place'],
                    'zone'      => $v['zone'],
                    'dateline'  => date('Y-m-d H:i:s', $v['dateline']),
                    'type'      => $v['type'],
                    'show'      => $v['show']
                ];
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
        if(!$dat){
            return FALSE;
        }
        $data = [
            'title'     => $dat['title'],
            'url'       => $dat['url'],
            'pic_url'   => $dat['pic_url'],
            'desc'      => $dat['desc'],
            'stime'     => strtotime($dat['stime']) ?: 0,
            'etime'     => strtotime($dat['etime']) ?: 0,
            'place'     => $dat['place'],
            'zone'      => $dat['zone'],
            'sort'      => $dat['sort'] ?: 0,
            'type'      => intval($dat['type']) ? self::TYPE_SCHOOL_RECRUIT : self::TYPE_SOCIAL_RECRUIT,
            'dateline'  => time(),
            'show'      => intval($dat['show']) ? self::STATUS_SHOW : self::STATUS_HIDE,
        ];
        if($id){
            $cond = ['id' => intval($id)];
            $res = $this->dbAdmin()->update(self::TABLE_JOB_FAIR, $data, $cond);
            if($res !== FALSE){
                return TRUE;
            }
            return FALSE;
        }else{
            return $this->dbAdmin()->insert(self::TABLE_JOB_FAIR, $data);
        }
    }

    /**
     * @param $ids
     * @return array|bool
     */
    public function getByIds($ids)
    {
        $ids = \clsTools::filterIds($ids);
        if(!$ids){
            return FALSE;
        }
        $cond = "id IN(" . implode(',', array_unique($ids)) . ")";
        $data = $this->dbAdmin()->fetch(self::TABLE_JOB_FAIR, '*', $cond, '', 0, 0, 'id');
        return $data;
    }

    /**
     * 获取单个校招信息
     * @param $id
     * @return array|bool
     */
    public function get($id)
    {
        if(!$id){
            return [];
        }
        $cond = ['id' => intval($id)];
        $data = $this->dbAdmin()->select(self::TABLE_JOB_FAIR, '*', $cond);
        if($data){
            $data['stime'] = $data['stime'] ? date('Y-m-d H:i', intval($data['stime'])) : '';
            $data['etime'] = $data['etime'] ? date('Y-m-d H:i', intval($data['etime'])) : '';
            $data['dateline'] = date('Y-m-d H:i', intval($data['dateline']));
        }
        return $data;
    }

    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function del($id)
    {
        if(!$id){
            return FALSE;
        }
        $cond = ['id' => intval($id)];
        $res = $this->get($id);
        if(!$res){
            return FALSE;
        }
        $ret = $this->dbAdmin()->delete(self::TABLE_JOB_FAIR, $cond);
        if($ret !== FALSE){
            return TRUE;
        }
        return FALSE;
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
        $cond = ['id' => intval($id)];
        $data = ['show' => $status ? 1 : 0];
        return $this->dbAdmin()->update(self::TABLE_JOB_FAIR, $data, $cond);
    }
}