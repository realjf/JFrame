<?php
/**
 * json对象操作
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/6
 * Time: 17:34
 */

namespace Module\Admin;


class mdlJson
{
    /**
     * json数组对象
     * @var array
     */
    public $jsonArr = array();
    /**
     * json字符串
     * @var string
     */
    public $jsonStr = "";

    /**
     * 构造函数，将数据库中取出的json字符串转化成json对象
     * @param $data
     */
    public function __construct($data)
    {
        if (empty($data)) {
            $this->jsonStr = "";
        } else {
            $this->jsonStr = $data;
        }
        return $this->toJsonObj();
    }

    /**
     * 获取子对象的所有id
     * @return array
     */
    public function getIdsAll()
    {
        $ids = [];
        if(is_array($this->jsonArr) && $this->jsonArr){
            foreach($this->jsonArr as $item){
                $ids[] = $item['id'];
            }
        }
        return $ids;
    }

    /**
     * 更新json数组对象
     * @param $sid
     * @param $data
     * @return int|mixed
     */
    public function updateObj($sid, $data)
    {
        $sid = intval($sid);
        $new = [];
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $new[$k] = \clsTools::removeXss($v);
                $new[$k] = \clsTools::slashes($v, 1);
            }
            $result = -1;
            $max['index'] = 0;
            $firstNode = is_array($this->jsonArr) && $this->jsonArr ? reset($this->jsonArr) : [];
            $max['id'] = $firstNode && isset($firstNode['id']) ? $firstNode['id'] : 0;
            if(is_array($this->jsonArr) && $this->jsonArr){
                foreach ($this->jsonArr as $k => $v) {
                    if (intval($v['id']) == $sid) {
                        $result = $k;
                        $new['id'] = $v['id'];
                    }
                    $diffres = array_diff($v, $new); // 判断是否是数据是否相同
                    if($diffres && isset($diffres['id']) && count($diffres) == 1){
                        $result = $k;
                        $new['id'] = $v['id'];
                    }
                    if(intval($v['id']) > $max['id']){
                        $max['id'] = $v['id'];
                        $max['index'] = $k;
                    }
                }
            }

            if ($result < 0) {
                $ids = count($this->jsonArr);
                if ($ids == 0) {
                    $new['id'] = 1;
                } else {
                    $new['id'] = intval($max['id'])+1;
                }

                // 添加新的数据
                if($this->jsonArr){
                    array_push($this->jsonArr, $new);
                }else{
                    $this->jsonArr[0] = $new;
                }
            } else {
                // 覆盖原来的数据
                foreach ($new as $k => $v) {
                    if(is_array($v)){
                        foreach($v as $kk => $vv){
                            $this->jsonArr[$result][$k][$kk] = $vv;
                        }
                    }else{
                        $this->jsonArr[$result][$k] = $v;
                    }
                }
//                $this->jsonArr[$result] = $new;
            }
        }
        return ['nums' => count($this->jsonArr), 'lastId' => $new['id']];
    }

    /**
     * 删除指定id的记录
     * @param $sid
     * @return bool
     */
    public function deleteObj($sid)
    {
        $sid = intval($sid);
        foreach ($this->jsonArr as $k => $v) {
            if (intval($v['id']) == $sid) {
                unset($this->jsonArr[$k]);
//                $this->toJsonString();
                return true;
            }
        }
        return false;
    }

    /**
     * 返回json数组对象
     * @return mixed
     */
    public function toJsonObj()
    {
        if (!empty($this->jsonStr)) {
            $this->jsonArr = json_decode($this->jsonStr, true);
        }else{
            $this->jsonArr = array();
        }
        return $this->jsonArr;
    }

    /**
     * 返回json字符串
     * @return string
     */
    public function toJsonString()
    {
        if (!empty($this->jsonArr)) {
            $new = array();
            foreach ($this->jsonArr as $key => $item) {
                foreach ($item as $k => $v)
                    $new[$key][$k] = \clsTools::removeXss(htmlspecialchars_decode($v));
            }
            $this->jsonArr = $new;
            $this->jsonStr = json_encode($this->jsonArr, JSON_UNESCAPED_UNICODE);
        }else{
            $this->jsonStr = "";
        }
        return $this->jsonStr;
    }
}