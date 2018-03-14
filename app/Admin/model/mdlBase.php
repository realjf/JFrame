<?php


namespace Module\Admin;


use Core\JFrame\BaseModel;

class mdlBase extends BaseModel
{

    /**
     * 移除字段前缀
     *
     * @param array  $fields
     * @param string $prefix
     * @return array
     */
    public function fieldPrefixRm($fields, $prefix = 's_')
    {
        $arr = array();
        $pattern = '/^' . preg_quote($prefix, '/') . '/'; // 转义正则表达式字符
        if(is_array($fields)){
            foreach($fields as $key => $val){
                if(!is_int($key)){
                    $key = preg_replace($pattern, '', $key);
                }
                if(is_array($val)){
                    $arr[$key] = $this->fieldPrefixRm($val, $prefix);
                } else{
                    $arr[$key] = $val;
                }
            }
        }
        return $arr;
    }
}