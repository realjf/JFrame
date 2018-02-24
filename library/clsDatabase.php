<?php

class clsDatabase extends \Library\Database\PdoMysql
{

    const DB_COND_EMPTY = '/** empty */'; // 为防止误操作，所有条件为空的情况，请传本参数进来

    /**
     * 插入单个或多个记录
     * @param string $table
     * @param array  $data
     * @param bool   $lastId
     * @param string $onDuplicate
     * @return bool|PDOStatement|string
     */
    public function insert($table, array $data, $lastId = TRUE, $onDuplicate = '')
    {
        return $this->_insert(TRUE, $table, $data, $lastId, $onDuplicate);
    }

    /**
     * 插入或替换单个记录或多个记录
     * @param string $table
     * @param array  $data
     * @return bool|PDOStatement|string
     */
    public function replace($table, array $data)
    {
        return $this->_insert(FALSE, $table, $data);
    }

    /**
     * 执行INSERT/REPLACE的实际逻辑
     * @param bool   $insert
     * @param string $table
     * @param array  $data
     * @param bool   $lastId
     * @param string $onDuplicate
     * @return bool|PDOStatement|string
     */
    private function _insert($insert, $table, array $data, $lastId = TRUE, $onDuplicate = '')
    {
        $data = self::parseData($data);
        if($data === FALSE){
            return FALSE;
        }
        list($cols, $values) = $data;
        $sql = sprintf("%s INTO %s %s VALUES %s", $insert ? 'INSERT' : 'REPLACE', $table, $cols, $values);
        if($insert && $onDuplicate){
            $sql .= " ON DUPLICATE KEY UPDATE {$onDuplicate}";
        }
        $result = $this->exec($sql);
        if($result && $insert && $lastId){
            return $this->lastInsertId();
        }
        return $result;
    }

    /**
     * 删除记录
     * @param string       $table
     * @param string|array $cond
     * @param int          $limit
     * @return bool|PDOStatement
     */
    public function delete($table, $cond, $limit = NULL)
    {
        $cond = self::mkCond($cond);
        if($cond === FALSE){
            return FALSE;
        }
        $limitStr = '';
        if($limit){
            $limit = intval($limit);
            if($limit > 0){
                $limitStr = " limit {$limit}";
            }
        }
        $sql = sprintf("DELETE FROM %s %s %s", $table, $cond, $limitStr);
        return $this->exec($sql);
    }

    /**
     * 根据条件获取记录数目
     * @param string       $table
     * @param string|array $cond
     * @param bool         $extract
     * @return bool|int
     */
    public function count($table, $cond, $extract = TRUE)
    {
        $cond = self::mkCond($cond, FALSE);
        if($cond === FALSE){
            return FALSE;
        }
        $sql = "SELECT count(*) as `rows` from {$table} {$cond}";
        if(!$extract){
            $sql = "explain {$sql}";
        }
        if($query = $this->query($sql)){
            $rs = $query->fetch();
            return intval($rs['rows']);
        }
        return FALSE;
    }

    /**
     * 更新数据表
     * @param string       $table
     * @param array        $data
     * @param array|string $cond
     * @param int          $limit
     * @return bool|PDOStatement
     */
    public function update($table, $data, $cond, $limit = 0)
    {
        if(empty($data)){
            return FALSE;
        }
        $values = array();
        if(is_array($data)){
            foreach($data as $k => $v){
                if(is_int($k)){
                    $values[] = $v;
                } else{
                    if(is_array($k) || is_array($v)){
                        $tmp = array(
                            'op'    => 'update',
                            'table' => $table,
                            'data'  => var_export($data, 1),
                            'uri'   => $_SERVER['REQUEST_URI']
                        );
//                        \clsLogger::write('db_slashes', clsLogger::ERROR, var_export($tmp, 1));
                    } else{
                        $values[] = "`" . self::slashes($k) . "` = '" . self::slashes($v) . "'";
                    }
                }
            }
            $data = implode(",", $values);
        }
        $sql = "update {$table} set {$data}";
        $cond = self::mkCond($cond, TRUE);
        if($cond === FALSE){
            $this->__halt(json_encode($cond), array(999, 999, "Conditions can't be empty!"));
            return FALSE;
        }
        $sql .= $cond;
        if($limit > 0){
            $sql .= " limit " . $limit;
        }
        return $this->exec($sql);
    }

    /**
     * 查询并返回单条记录
     * @param string       $table
     * @param string       $fields
     * @param string|array $cond
     * @param string       $order
     * @param int          $start
     * @return array|bool
     */
    public function select($table, $fields = NULL, $cond = self::DB_COND_EMPTY, $order = NULL, $start = NULL)
    {
        if(empty($fields)){
            $fields = '*';
        } elseif(!is_string($fields)){
            $this->__halt(json_encode($fields), array(999, 999, "Fields must be string!"));
            return [];
        } elseif(strpos($fields, '=') !== FALSE){
            $this->__halt(json_encode($fields), array(999, 999, "Fields has string '='"));
            return [];
        }
        $sql = "select {$fields} from {$table}";

        $cond = self::mkCond($cond, FALSE);
        if($cond === FALSE){
            $this->__halt(json_encode($cond), array(999, 999, "Conditions can't be empty!"));
            return [];
        }
        $sql .= $cond;

        if($order){
            $sql .= " order by {$order} ";
        }
        return $this->sqlSelect($sql, $start);
    }

    /**
     * 查询并返回多条记录
     * @param string       $table
     * @param string       $fields
     * @param string|array $cond
     * @param string       $order
     * @param int          $limit
     * @param int          $start
     * @param string       $priKey
     * @return array
     */
    public function fetch($table, $fields = NULL, $cond = self::DB_COND_EMPTY, $order = NULL, $limit = NULL, $start = NULL, $priKey = NULL)
    {
        if(empty($fields)){
            $fields = '*';
        } elseif(!is_string($fields)){
            $this->__halt(json_encode($fields), array(999, 999, "Fields must be string!"));
            return [];
        } elseif(strpos($fields, '=') !== FALSE){
            $this->__halt(json_encode($fields), array(999, 999, "Fields has string '='"));
            return [];
        }

        $sql = "select {$fields} from {$table}";
        $cond = self::mkCond($cond, FALSE);
        if($cond === FALSE){
            $this->__halt(json_encode($cond), array(999, 999, "Conditions can't be empty!"));
            return [];
        }
        $sql .= $cond;
        if($order){
            $sql .= " order by {$order}";
        }
        $start = intval($start);
        $limit = intval($limit);
        if($start > 0 || $limit > 0){
            $sql .= " limit ";
            if($start > 0){
                $sql .= $start . ",";
            }
            if($limit > 0){
                $sql .= $limit;
            }
        }
        return $this->sqlFetch($sql, $priKey);
    }

    /**
     * 查询并返回多条记录，且包含分页信息
     * @param string $table
     * @param string $fields
     * @param string $cond
     * @param string $order
     * @param int    $limit
     * @param int    $page
     * @param string $priKey
     * @return array|bool
     */
    public function fetchWithPage($table, $fields = NULL, $cond = self::DB_COND_EMPTY, $order = NULL, $limit = 20, $page = 0, $priKey = NULL)
    {
        $count = $this->count($table, $cond);
        if($count === FALSE){
            return FALSE;
        }
        $result = clsTools::multi($count, $limit, $page);
        if($result['count'] > 0){
            $result['data'] = $this->fetch($table, $fields, $cond, $order, $result['perpage'], $result['start'], $priKey);
        }
        return $result;
    }

    /**
     * 获取表中两个字段并返回对应的 key - value 数组
     * @param string $table
     * @param string $fields -- 构成 key - value的两个字段，key必须在前面，value在后面
     * @param string $cond
     * @param string $order
     * @param int    $limit
     * @param int    $start
     * @return array|bool
     */
    public function fetchKeyValue($table, $fields = NULL, $cond = self::DB_COND_EMPTY, $order = NULL, $limit = NULL, $start = NULL)
    {
        $column = explode(",", $fields);
        if(count($column) != 2){
            return FALSE;
        }
        $key = $column[0];
        $value = $column[1];
        $data = $this->fetch($table, $fields, $cond, $order, $limit, $start);
        $result = array();
        foreach($data as $v){
            $result[$v[$key]] = $v[$value];
        }
        return $result;
    }

    /**
     * 将记录从 $fromTable 移动到 $toTable
     * @param string       $fromTable
     * @param string       $toTable
     * @param string|array $cond
     * @return bool|PDOStatement
     */
    public function move($fromTable, $toTable, $cond)
    {
        if(is_array($fromTable)){
            $fldFrom = $fromTable[1];
            $tblFrom = $fromTable[0];
        } else{
            $fldFrom = '*';
            $tblFrom = $fromTable;
        }
        if($this->copy(array($tblFrom, $fldFrom), $toTable, $cond)){
            return $this->delete($tblFrom, $cond);
        }
        return FALSE;
    }

    /**
     * 将记录从 $fromTable 复制到 $toTable
     * @param string|array $fromTable
     * @param string|array $toTable
     * @param string|array $cond
     * @return PDOStatement
     */
    public function copy($fromTable, $toTable, $cond)
    {
        $cond = self::mkCond($cond);
        if(is_array($fromTable)){
            $fldFrom = $fromTable[1];
            $tblFrom = $fromTable[0];
        } else{
            $fldFrom = '*';
            $tblFrom = $fromTable;
        }
        if(is_array($toTable)){
            $fldTo = "(" . $toTable[1] . ")";
            $tblTo = $toTable[0];
        } else{
            $fldTo = '';
            $tblTo = $toTable;
        }
        $sql = "insert into {$tblTo}{$fldTo}";
        $sql .= " select {$fldFrom} from $tblFrom {$cond}";
        return $this->exec($sql);
    }

    /**
     * 取单个记录中的某个字段的值
     * @param string $table
     * @param string $column
     * @param string $cond
     * @param string $order
     * @param int    $start
     * @return bool
     */
    public function one($table, $column = NULL, $cond = self::DB_COND_EMPTY, $order = NULL, $start = NULL)
    {
        $rs = $this->select($table, $column, $cond, $order, $start);
        if($rs){
            return current($rs);
        }
        return FALSE;
    }

    /**
     * 直接传入 SQL 获取单条记录
     * @param string $sql
     * @param int    $start
     * @return array|bool
     */
    public function sqlSelect($sql, $start = 0)
    {
        if($start && $start > 0){
            $sql .= " limit {$start},1";
        } else{
            $sql .= " limit 1";
        }
        if($query = $this->query($sql)){
            return $query->fetch() ?: FALSE;
        }
        return FALSE;
    }

    /**
     * 直接传入SQL获取多条记录
     * @param string $sql
     * @param string $priKey
     * @return array
     */
    public function sqlFetch($sql, $priKey = NULL)
    {
        $data = array();
        if($query = $this->query($sql)){
            $result = $query->fetchAll() ?: array();
            if($priKey){
                foreach($result as $res){
                    $data[$res[$priKey]] = $res;
                }
            } else{
                $data = $result;
            }
        }
        return $data;
    }

    /**
     * 直接传入 SQL 获取某个记录的单个字段
     * @param string $sql
     * @return array
     */
    public function sqlOne($sql)
    {
        $data = $this->sqlSelect($sql);
        if(is_array($data)){
            return reset($data);
        }
        return FALSE;
    }

    /**
     * 构造分页信息
     * @param int $count
     * @param int $number
     * @param int $page
     * @return array
     */
    static public function multi($count, $number = 20, $page = 1)
    {
        $result = array();
        $result['perpage'] = $result['number'] = max(1, intval($number));;
        $result['count'] = max(0, intval($count));
        $result['pagecount'] = $count ? ceil($result['count'] / $result['number']) : 1;
        $result['page'] = intval($page);
        if($result['page'] > $result['pagecount']){
            $result['page'] = $result['pagecount'];
        }
        if($result['page'] <= 1){
            $result['page'] = 1;
        }
        $result['start'] = ($result['page'] - 1) * $result['number'];
        $result['mark'] = $result['start'] + 1;
        $result['data'] = array();
        return $result;
    }

    /**
     * 构造条件参数
     * @param string|array $cond
     * @param bool         $emptyCheck
     * @return string
     */
    static public function mkCond($cond, $emptyCheck = TRUE)
    {
        if($cond == self::DB_COND_EMPTY){
            return '';
        }
        if($emptyCheck && empty($cond)){
            return FALSE;
        }
        if(is_array($cond)){
            $tmp = array();
            foreach($cond as $col => $val){
                if(is_int($col)){
                    $tmp[] = $val;
                } else{
                    $tmp[] = sprintf("`%s` = '%s'", $col, self::slashes($val));
                }
            }
            $cond = implode(" and ", $tmp);
        }
        $cond = trim($cond);
        if($cond){
            $cond = " where {$cond}";
        }
        return $cond;
    }

    /**
     * 解析INSERT/REPLACE传入的一维或二维数组
     * @param array $data
     * @return array|bool
     */
    static public function parseData(array $data)
    {
        if(!is_array(reset($data))){
            $data = array($data);
        }
        $first = reset($data);
        if(empty($first)){
            return FALSE;
        }
        $cols = '(' . sprintf("`%s`", implode("`,`", array_keys($first))) . ')';
        $values = array();
        foreach($data as $dat){
            $vals = array();
            foreach($dat as $val){
                if(is_array($val)){
                    $tmp = array(
                        'op'   => 'insert',
                        'data' => var_export($data, 1),
                        'uri'  => $_SERVER['REQUEST_URI']
                    );
//                    \clsLogger::write('db_slashes', clsLogger::ERROR, var_export($tmp, 1));
                } else{
                    $vals[] = sprintf("'%s'", self::slashes($val));
                }
            }
            $values[] = implode(",", $vals);
        }
        $values = "(" . implode("),(", $values) . ")";
        return array($cols, $values);
    }

    /**
     * 字符转义
     * @param mixed $var
     * @return float|int|string
     */
    static public function slashes($var)
    {
        if(is_null($var)){
            return 'null';
        } elseif(is_int($var)){
            return intval($var);
        } elseif(is_bool($var)){
            return $var ? 'true' : 'false';
        } elseif(is_float($var)){
            return floatval($var);
        }

        return addslashes($var);
    }
}