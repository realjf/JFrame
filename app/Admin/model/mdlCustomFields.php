<?php


namespace Module\Admin;


class mdlCustomFields extends mdlBase
{
    const TBL_CUSTOM_FIELDS = "custom_fields";

    const CUSTOM_TYPE_SNAPSHOT = "snapshot";
    const CUSTOM_TYPE_LIBRARY = "library";

    public static $keys = [
        'family'    => 'family',
        'other'     => 'other',
    ];

    /**
     * 保存
     * @param string $key 字段键名
     * @param mixed $value 值
     * @param int $custom_id 简历id
     * @param string $custom_type 快照or用户简历
     * @param string $cond 根据条件更新
     * @return int
     */
    public function save($key, $value, $custom_id, $custom_type, $cond = '')
    {
        if(!in_array($key, self::$keys)){
            return false;
        }
        if($custom_id <= 0 || $custom_type == ''){
            return false;
        }
        if($value){
            $data['type'] = $key;
            $data['value'] = str_replace('/[\s]+/i', ' ', trim($value));
            $data['custom_id'] = intval($custom_id);
            $data['custom_type'] = $custom_type;
            if($cond){
                return $this->dbAdmin()->update(self::TBL_CUSTOM_FIELDS, $data, $cond);
            } else{
                $ret = $this->dbAdmin()->select(self::TBL_CUSTOM_FIELDS, '*', $data);
                if(!$ret){
                    $duplicate = " UPDATE type='".$key."', value='".$value."', custom_id=".intval($custom_id).", custom_type='".$custom_type."'";
                    return $this->dbAdmin()->insert(self::TBL_CUSTOM_FIELDS, $data, $duplicate);
                }else{
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * 根据uid更新，如果不存在，添加新值
     * @param $uid
     * @param $key
     * @param $value
     * @return int
     */
    public function updateSnapshotByUid($uid, $key, $value)
    {
        $resumeIds = mdlApplyModel::instance()->getResumeStatus($uid);
        $resumeIds = $resumeIds ? \clsTools::mkKey($resumeIds, 'resume_id', true) : [];
        if(!$resumeIds){
            return false;
        }
        if(!in_array($key, self::$keys)){
            return false;
        }
        $cond = [
            "type='" . $key . "'",
            'custom_id IN (' . implode(',', $resumeIds) . ')',
            "custom_type='" . self::CUSTOM_TYPE_SNAPSHOT . "'",
        ];

        $oldData = $this->dbAdmin()->fetch(self::TBL_CUSTOM_FIELDS, '*', $cond);
        $insertData = [];
        $oldRids = $oldData ? \clsTools::mkKey($oldData, 'custom_id', true) : [];
        $insIds = array_diff($resumeIds, $oldRids);
        if($insIds){
            foreach($insIds as $iRid){
                $insertData[] = [
                    'type'   => $key,
                    'custom_id' => intval($iRid),
                    'custom_type' => self::CUSTOM_TYPE_SNAPSHOT,
                    'value' => str_replace('/[\s]+/i', ' ', trim($value))
                ];
            }
        }

        if($insertData){
            $this->dbAdmin()->insert(self::TBL_CUSTOM_FIELDS, $insertData);
        }
        $data['value'] = str_replace('/[\s]+/i', ' ', trim($value));
        return $this->dbAdmin()->update(self::TBL_CUSTOM_FIELDS, $data, $cond);
    }

    /**
     * 删除
     * @param $id
     * @return int
     */
    public function delById($id)
    {
        return $this->dbAdmin()->delete(self::TBL_CUSTOM_FIELDS, ['id'=>intval($id)]);
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getById($id)
    {
        $cond = "id = " . intval($id);
        return $this->dbAdmin()->select(self::TBL_CUSTOM_FIELDS, '*', $cond);
    }

    /**
     * @param $cond ['custom_type', 'type', 'custom_id']
     * @param string $fields
     * @param bool $jsonDecode
     * @param int $limit
     * @return array|bool
     */
    public function getByCond($cond, $fields = '*', $jsonDecode = true, $limit = 1)
    {
        if($cond){
            $data = $this->dbAdmin()->fetch(self::TBL_CUSTOM_FIELDS, $fields, $cond);
            if($data && $jsonDecode){
                foreach($data as &$v){
                    $v['value'] = !empty($v['value']) ? json_decode($v['value'], 1) : [];
                    $v['value'] = is_array($v['value']) ? $v['value'] : [];
                }
            }
            if($data && $limit == 1){
                $data = reset($data);
            }
            return $data;
        }
        return FALSE;
    }
}