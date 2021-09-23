<?php

class clsLogger
{
    //单例模式  
    private static $instance    = NULL;
    //文件句柄  
    private static $handle      = NULL;
    //日志开关  
    private $log_switch     = NULL;
    //日志相对目录  
    private $log_file_path      = NULL;
    //日志文件最大长度，超出长度重新建立文件  
    private $log_max_len        = NULL;
    //日志文件前缀,入 log_0  
    private $log_file_pre       = 'log_';
    private $log_file_date      = "";// 日期

    // 目录权限 777
    const LOG_FILE_PATH = "/tmp/4399zhaopin/";
    const LOG_SWITCH = true;
    const LOG_MAX_LEN = 1000000;


    /** 
     * 构造函数 
     *  
     */
    protected function __construct()
    {   
        $this->log_file_path     = self::LOG_FILE_PATH;
        $this->log_switch        = self::LOG_SWITCH;
        $this->log_max_len       = self::LOG_MAX_LEN;
        $this->$log_file_date    = date("Ymd") . "_";
    }

    /** 
     * 单利模式 
     */
    public static function get_instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 记录日志
     */
    static public function write($desc, $time = "")
    {
        $L = clsLogger::get_instance();
        $L->log(1, json_encode($desc, JSON_UNESCAPED_UNICODE), $time ?: date('Y-n-j H:m:s'));  
        $L->close();
    }

    /** 
     *  
     * 日志记录 
     *  
     * @param int $type  0 -> 记录(THING LOG) / 1 -> 错误(ERROR LOG) 
     * @param string $desc 
     * @param string $time 
     *  
     *  
     */
    public function log($type, $desc, $time)
    {
        if ($this->log_switch) {

            if (self::$handle == NULL) {
                $filename = $this->log_file_pre . $this->$log_file_date . $this->get_max_log_file_suf();
                self::$handle = fopen($this->log_file_path . $filename, 'a');
            }
            switch ($type) {
                case 0:
                    fwrite(self::$handle, 'THING LOG:' . ' ' . $desc . ' ' . $time . "\n");
                    break;
                case 1:
                    fwrite(self::$handle, 'ERROR LOG:' . ' ' . $desc . ' ' . $time . "\n");
                    break;
                default:
                    fwrite(self::$handle, 'THING LOG:' . ' ' . $desc . ' ' . $time . "\n");
                    break;
            }
        }
    }

    /** 
     * 获取当前日志的最新文档的后缀 
     *  
     */
    private function get_max_log_file_suf()
    {
        $log_file_suf = null;
        if (is_dir($this->log_file_path)) {
            if ($dh = opendir($this->log_file_path)) {
                while (($file = readdir($dh)) != FALSE) {
                    if ($file != '.' && $file != '..') {
                        if (filetype($this->log_file_path . $file) == 'file') {
                            $rs = mb_split('_', $file);
                            if ($log_file_suf < $rs[1]) {
                                $log_file_suf = $rs[1];
                            }
                        }
                    }
                }

                if ($log_file_suf == NULL) {
                    $log_file_suf = 0;
                }
                //截断文件  
                if (file_exists($this->log_file_path . $this->log_file_pre . $this->$log_file_date . $log_file_suf) && filesize($this->log_file_path . $this->log_file_pre . $this->$log_file_date . $log_file_suf) >= $this->log_max_len) {
                    $log_file_suf = intval($log_file_suf) + 1;
                }

                return $log_file_suf;
            }
        }

        return 0;
    }

    /** 
     * 关闭文件句柄 
     *  
     */
    public function close()
    {
        fclose(self::$handle);
    }
}
