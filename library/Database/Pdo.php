<?php
/**
 * @author flycat
 * @since  2011-12-14
 */
namespace Library\Database;

class Pdo
{
    /**
     * 连接失败时自动重试次数
     * @var int
     */
    public $autoRetry = 1;

    /**
     * SQL执行数量
     *
     * @var int
     */
    private static $_counter = 0;

    private $_pdoInstance = null;
    private $_dbDns = '';
    private $_dbUser = '';
    private $_dbPass = '';
    private $_options = array();

    /**
     * @param string $dns
     * @param string $user
     * @param string $pass
     * @param array $options
     */
    public function __construct($dns, $user, $pass, $options = array())
    {
        $this->_dbDns = $dns;
        $this->_dbUser = $user;
        $this->_dbPass = $pass;
        $this->_options = $options;
        $this->_pdoInstance = $this->_open();
    }

    private function _open()
    {
        return new \PDO($this->_dbDns, $this->_dbUser, $this->_dbPass, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names '{$this->_options["charset"]}'",
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ));
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->_pdoInstance = null;
    }

    /**
     * @param string $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        if (!$this->_pdoInstance) {
            $this->_pdoInstance = $this->_open();
        }
        return $this->_pdoInstance->query($sql);
    }

    /**
     * @param string $sql
     * @return int
     */
    public function exec($sql)
    {
        if (!$this->_pdoInstance) {
            $this->_pdoInstance = $this->_open();
        }
        return $this->_pdoInstance->exec($sql);
    }

    /**
     * @return bool|int
     */
    public function insertId()
    {
        if (!$this->_pdoInstance) {
            return false;
        }
        return $this->_pdoInstance->lastInsertId();
    }

    /**
     * @return string
     */
    public function error()
    {
        if (!$this->_pdoInstance) {
            return false;
        }
        return $this->_pdoInstance->errorInfo();
    }

    /**
     * @return int
     */
    public function errno()
    {
        if (!$this->_pdoInstance) {
            return false;
        }
        return $this->_pdoInstance->errorCode();
    }

    /**
     * @param string $string
     * @return string
     */
    protected function __slashes($string)
    {
        return addslashes(stripslashes($string));
    }

    /**
     * 返回SQL执行数量
     *
     * @return int
     */
    public function counter()
    {
        return self::$_counter;
    }
}
