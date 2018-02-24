<?php

namespace Library\Database;

class PdoMysql
{
    /**
     * @var bool
     */
    private $_inTransaction = FALSE;

    /**
     * @var array
     */
    private $_instances = array();

    /**
     * @var \PDO
     */
    private $_lastInstance = NULL;

    /**
     * @var array
     */
    private $_options = array();

    /**
     * @var array
     */
    private $_servers = array();

    /**
     * @var string
     */
    private $_name = '';

    /**
     * 普通模式sql执行慢行时间
     * @var int
     */
    private $_slowTimer = 50;

    /**
     * cron模式sql执行慢行时间
     * @var int
     */
    private $_cronSlowTimer = 1000;

    private $_counter = 0;

    /**
     * 日志文件命名的后缀，区分cron，普通模式
     * @var string
     */
    private $_logSuffix = '';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $dns = sprintf("mysql:dbname=%s;host=%s;port=%s", $config['dbname'], $config['host'], $config['port'] ?: '3306');
        $this->_options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names '{$config["charset"]}'",
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        );
        $this->_servers[] = array('dns' => $dns, 'user' => $config['user'], 'pass' => $config['pass'], $this->_options);
        $this->_lastInstance = new \PDO($dns, $config['user'], $config['pass'], $this->_options);
        $this->_instances[] = $this->_lastInstance;
        $this->_name = empty($config['name']) ? ('_' . $config['dbname']) : ('_' . $config['name']);

//        if(SYS_BOOSTRAP_MODE == 1){ //cron模式
//            $this->_slowTimer = $this->_cronSlowTimer;
//            $this->_logSuffix = '_cron';
//        }
    }

    /********* For Transaction ********/
    public function beginTransaction()
    {
        $instance = $this->_getInstance(TRUE);
        $res = $instance->beginTransaction();
        $this->_inTransaction = $instance->inTransaction();;
        return $res;
    }

    public function commit()
    {
        $instance = $this->_getInstance(TRUE);
        $res = $instance->commit();
        $this->_inTransaction = $instance->inTransaction();;
        return $res;
    }

    public function inTransaction()
    {
        return $this->_inTransaction;
    }

    public function rollBack()
    {
        $instance = $this->_getInstance(TRUE);
        $res = $instance->rollBack();
        $this->_inTransaction = $instance->inTransaction();;
        return $res;
    }

    /********* For Error ********/
    public function errorCode()
    {
        return $this->_getLastInstance()->errorCode();
    }

    public function errorInfo()
    {
        return $this->_getLastInstance()->errorInfo();
    }

    /********* For Options ********/
    /**
     * @param $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->_getLastInstance()->getAttribute($attribute);
    }

    public function setAttribute($attribute, $value)
    {
        return $this->_getLastInstance()->setAttribute($attribute, $value);
    }

    static public function getAvailableDrivers()
    {
        return \PDO::getAvailableDrivers();
    }

    /********* For Query ********/

    /**
     * @param $statement
     * @return \PDOStatement
     */
    public function exec($statement)
    {
        $this->_debugSqlTrace($statement);
        //$isWrite = self::_isWrite($statement);
        $timer = microtime(1);
        $result = $this->_getInstance(TRUE)->exec($statement);
        $timer = 1000 * (microtime(1) - $timer);
        if($result === FALSE){
            $this->__halt($statement, $this->errorInfo());
        } elseif($timer > $this->_slowTimer){
            $this->_logSlowQuery($statement, $timer);
        }
        return $result;
    }

    public function lastInsertId($name = NULL)
    {
        return $this->_getInstance(TRUE)->lastInsertId($name);
    }

    public function query($statement)
    {
        $this->_debugSqlTrace($statement);
        $timer = microtime(1);
        $isWrite = self::_isWrite($statement);
        $result = $this->_getInstance($isWrite)->query($statement);
        $timer = 1000 * (microtime(1) - $timer);
        if($result === FALSE){
            $this->__halt($statement, $this->errorInfo());
        } elseif($timer > $this->_slowTimer){
            $this->_logSlowQuery($statement, $timer);
        }
        return $result;
    }

    public function prepare($statement, $driver_options = NULL)
    {
        $isWrite = self::_isWrite($statement);
        return $this->_getInstance($isWrite)->prepare($statement, $driver_options);
    }

    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        return $this->_getInstance()->quote($string, $parameter_type);
    }

    private function _isWrite($statment)
    {
        return preg_match('/^(update|insert|replace|create|delete|truncate|drop|alter)\s/i', trim($statment)) || $this->inTransaction();
    }

    /**
     * @param bool $write
     * @return \PDO
     */
    private function _getInstance($write = FALSE)
    {
        return $write ? $this->_lastInstance : $this->_lastInstance;
    }

    /**
     * @return \PDO
     */
    private function _getLastInstance()
    {
        return $this->_lastInstance;
    }

    protected function __halt($sql, array $errInfo)
    {
        $message = sprintf("[%s]\t%d(%d)\t%s\ngit:\t%s\n\t%s\n", date('H:i:s'), $errInfo[0], $errInfo[1], $errInfo[2], $_SERVER['REQUEST_URI'], $sql);
        $file = "/tmp/mysql_error_" . date('Ymd') . ".log";
        file_put_contents($file, $message, FILE_APPEND);

        $backtrace = debug_backtrace();
        array_shift($backtrace);
        foreach($backtrace as $i => $l){
            $message .= sprintf("[%d] in function %s%s%s in %s on line %d\n",
                $i, $l['class'], $l['type'], $l['function'], $l['file'], $l['line']);
        }
//        $level = clsLogger::ERROR;
//        if (preg_match("#Duplicate entry.*for key#", $errInfo[2])) {
//            $level = clsLogger::WARNING;
//        }
//        clsLogger::write('mysql', $level, $this->_name . " " . $message);
        return;
    }

    /**
     * 慢行sql记录
     * @param string $sql
     * @param int    $timer
     * @return bool
     */
    private function _logSlowQuery($sql, $timer)
    {
        $message = sprintf("%10.3f\t%s", $timer, $sql);
//        clsLogger::write('mysql_slow', clsLogger::WARNING, $this->_name . " " . $message);
        return TRUE;
    }

    /**
     * @param $sql
     * @return bool
     */
    private function _debugSqlTrace($sql)
    {
        $this->_counter++;
        if(isset($_COOKIE["__mysql__"])){
            static $firstRecord = FALSE;
            $str = "";
            if(!$firstRecord){
                $url = ($_SERVER['HTTP_HOST'] && $_SERVER['REQUEST_URI']) ? ("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : '_cmd_';
                $str = sprintf("[Url:%s]\n", $url);
                $firstRecord = TRUE;
            }
            $str .= sprintf("[%s@%d]%s;\n", date("Y-m-d H:i:s"), $this->_counter, preg_replace('#\s+#', ' ', $sql));
            file_put_contents("/tmp/my_query.sql", $str, FILE_APPEND);
        }
        return TRUE;
    }
}
