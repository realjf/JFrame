<?php
/**
 * Enter description here ...
 *
 * @author flycat
 * @since  2011-12-14
 */
namespace Library\Database;

class Database extends Pdo
{
    /**
     *
     */
    const DB_EMPTY = '/*DB_EMPTY*/';

    static $_instances = [];

    static public function getInstance($dns, $user, $pass)
    {
        $key = md5("{$dns}.{$user}.{$pass}");
        if (!isset(self::$_instances[$key])) {
            $dbInstance = new self($dns, $user, $pass);
            $_instances[$key] = &$dbInstance;
        } else {
            $dbInstance = &$_instances[$key];
        }
        return $dbInstance;
    }

    /**
     * 执行 INSERT/REPLACE 操作
     *
     * @param string $table
     * @param array $data
     * @param boolean $replace
     * @param string $onDuplicate
     * @return int|bool
     */
    private function _insert($table, $data = array(), $replace = FALSE, $onDuplicate = '')
    {
        $res = $this->_parseData($data);
        if (empty($res['values'])) {
            return FALSE;
        }
        $op = $replace ? 'REPLACE' : 'INSERT';
        $sql = sprintf("%s INTO %s %s VALUES %s", $op, $table, $res['column'], $res['values']);

        if ($onDuplicate && !$replace) {
            $sql .= " ON DUPLICATE KEY " . $onDuplicate;
        }
        return $this->exec($sql);
    }

    /**
     * 执行INSERT操作
     *
     * @param string $table
     * @param array $data
     * @param string $onDuplicate
     * @return int
     */
    public function insert($table, $data = array(), $onDuplicate = '')
    {
        if ($this->_insert($table, $data, FALSE, $onDuplicate)) {
            return $this->insertId();
        }
        return false;
    }

    /**
     * 执行 REPLACE 操作
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public function replace($table, $data = array())
    {
        return $this->_insert($table, $data, TRUE);
    }

    /**
     * 格式化 INSERT/REPLACE 数组
     *
     * @param array $data
     * @return array
     */
    private function _parseData($data)
    {
        $res = array();
        $deep = $this->_getArrayDeep($data);
        if ($deep == 0) {
            return $res;
        } elseif ($deep == 1) {
            $data = array($data);
        }
        $values = array();
        $first = reset($data);
        $column = $this->_makeColumns($first);
        foreach ($data as $dat) {
            $values[] = $this->_makeValues($dat);
        }
        $res['column'] = $column;
        $res['values'] = implode(",", $values);
        return $res;
    }

    /**
     * 判断数组的维数，最多只判断二维
     * @param array $data
     * @return int
     */
    private function _getArrayDeep($data)
    {
        if (!is_array($data) || empty($data)) {
            return 0;
        } elseif (is_array(end($data))) {
            return 2;
        }
        return 1;
    }

    /**
     * 够在 INSERT/REPLACE 的VALUES部分内容
     *
     * @param array $data
     * @return string
     */
    private function _makeValues($data)
    {
        $dat = array();
        foreach ($data as $v) {
            $dat[] = sprintf("'%s'", self::slashes($v));
        }
        return sprintf("(%s)", implode(", ", $dat));
    }

    /**
     * 够在 INSERT/REPLACE 的COLUMN部分内容
     *
     * @param array $data
     * @return string
     */
    private function _makeColumns($data)
    {
        $dat = array();
        foreach ($data as $k => $v) {
            if (is_numeric($k)) {
                return '';
            }
            $tmp = trim(self::slashes($k));
            if ($tmp{0} != '`') {
                $tmp = "`{$tmp}`";
            }
            $dat[] = $tmp;
        }
        return sprintf("(%s)", implode(", ", $dat));
    }

    /**
     * 更新表数据
     * @param string $table
     * @param array $data
     * @param string $cond
     * @param int $limit
     * @return int
     */
    public function update($table, $data = array(), $cond = '', $limit = 0)
    {
        if (empty($data)) {
            return FALSE;
        }
        $values = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_int($k)) {
                    $values[] = $v;
                } else {
                    $values[] = "`" . self::slashes($k) . "` = '" . self::slashes($v) . "'";
                }
            }
            $data = implode(",", $values);
        }
        $sql = "update {$table} set {$data}";
        $cond = $this->_makeCondition($cond, FALSE);
        if ($cond === FALSE) {
            return $this->__halt("Conditions can't be empty!");
        }
        $sql .= $cond;
        if ($limit > 0) {
            $sql .= " limit " . $limit;
        }
//        var_dump($sql);
        return $this->exec($sql);
    }

    /**
     * 删除记录
     * @param string $table
     * @param string|array $cond
     * @param int $limit
     * @return int
     */
    public function delete($table, $cond = '', $limit = 0)
    {
        $sql = "delete from {$table}";
        if (empty($cond)) { // 为防止误操作, 条件不允许为空
            return FALSE;
        }
        $cond = $this->_makeCondition($cond, FALSE);
        if ($cond === FALSE) {
            return $this->__halt("Conditions can't be empty!");
        }
        $sql .= $cond;
        if ($limit) {
            $sql .= " limit {$limit}";
        }
        return $this->exec($sql);
    }

    /**
     * 查询单条记录
     * @param string $table
     * @param string $field
     * @param string|array $cond
     * @param string $order
     * @return array|bool
     */
    public function select($table, $field = '*', $cond = '', $order = '')
    {
        if (empty($field)) {
            $field = $field = '*';
        }
        $sql = "select {$field} from {$table}";

        $cond = $this->_makeCondition($cond, TRUE);
        if ($cond === FALSE) {
            return $this->__halt("Conditions can't be empty!");
        }
        $sql .= $cond;


        if ($order) {
            $sql .= " order by {$order} ";
        }

        //var_dump($sql);
        return $this->sqlSelect($sql);
    }

    /**
     * 获取统计
     * @param string $table
     * @param string|array $cond
     * @param bool $exact
     * @return int
     */
    public function count($table, $cond = '', $exact = TRUE)
    {
        $sql = $exact ? "select count(1) as `rows`" : "explain select 1";

        $cond = $this->_makeCondition($cond, TRUE);
        if ($cond === FALSE) {
            $this->__halt("Conditions can't be empty!");
            return 0;
        }
        $sql .= " from {$table} {$cond}";
        $res = $this->sqlSelect($sql);
        return max(intval($res['rows']), 0);
    }

    /**
     * 查询多条记录
     * @param string $table
     * @param string $fields
     * @param string $cond
     * @param string $order
     * @param int $limit
     * @param int $start
     * @param string $primaryKey
     * @return array
     */
    public function fetch($table, $fields = '*', $cond = '', $order = '', $limit = 0, $start = 0, $primaryKey = '')
    {
        $data = array();
        if (empty($fields)) {
            $fields = '*';
        }
        $sql = "select {$fields} from {$table}";

        $cond = $this->_makeCondition($cond, TRUE);
        if ($cond === FALSE) {
            $this->__halt("Conditions can't be empty!");
            return $data;
        }
        $sql .= $cond;

        if ($order) {
            $sql .= " order by {$order}";
        }
        $start = intval($start);
        $limit = intval($limit);
        if ($start > 0 || $limit > 0) {
            $sql .= " limit ";
            if ($start > 0) {
                $sql .= $start . ",";
            }
            if ($limit > 0) {
                $sql .= $limit;
            }
        }
//        var_dump($sql);
        return $this->sqlFetch($sql, $primaryKey);
    }

    /**
     * 转义非法字符
     * @param string $str
     * @return string
     */
    static public function slashes($str)
    {
        return addslashes(stripslashes($str));
    }

    /**
     * Enter description here ...
     *
     * @param string $table
     * @param string $field
     * @param string $cond
     * @param string $order
     * @return array|null
     */
    public function one($table, $field, $cond = '', $order = '')
    {
        $data = $this->select($table, $field, $cond, $order);
        if (is_array($data)) {
            return reset($data);
        }
        return FALSE;
    }

    /**
     * 组合条件语句
     * @param string|array $cond
     * @param boolean $allowEmpty
     * @return string
     */
    private function _makeCondition($cond = self::DB_EMPTY, $allowEmpty = TRUE)
    {
        if ($cond == self::DB_EMPTY) {
            return '';
        } elseif ($cond) {
            if (is_array($cond)) {
                foreach ($cond as $k => $v) {
                    if (!is_numeric($k)) {
                        $cond[$k] = "`{$k}` = '" . self::slashes($v) . "'";
                    }
                }
                $cond = implode(" and ", $cond);
            }
            return " where {$cond}";
        } elseif ($allowEmpty) {
            return '';
        }
        return FALSE;
    }

    /**
     * @param string $sql
     * @param int $type
     * @return array|bool
     */
    public function sqlSelect($sql, $type = \PDO::FETCH_ASSOC)
    {
        $sql .= " limit 1";
        $q = $this->query($sql);
        return $q->fetch($type);
    }

    /**
     * @param string $sql
     * @param null $primaryKey
     * @return array
     */
    public function sqlFetch($sql, $primaryKey = NULL)
    {
        $data = $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if ($primaryKey && $first = reset($data)) {
            if (isset($first[$primaryKey])) {
                $result = array();
                foreach ($data as $dat) {
                    $result[$dat[$primaryKey]] = $dat;
                }
                return $result;
            }
        }
        return $data;
    }

    /**
     * @param string $sql
     * @return array
     */
    public function sqlOne($sql)
    {
        $data = $this->sqlSelect($sql);
        if (is_array($data)) {
            return reset($data);
        }
        return FALSE;
    }

    protected function __halt($message)
    {
        exit($message);
    }
}
