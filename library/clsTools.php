<?php


class clsTools
{
    /**
     * 获取IP
     * @param bool $number
     * @return string|int
     */
    static public function getIp($number = FALSE)
    {
        static $ip = NULL;
        if(empty($ip)){
            $keys = array(
                'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_REMOTE_HOST', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'
            );
            $found = FALSE;
            foreach($keys as $key){
                if(isset($_SERVER[$key]) && $_SERVER[$key]){
                    $theIp = trim($_SERVER[$key]);
                    $ips = array();
                    if(preg_match_all('/(\d{1,3}(?:\.\d{1,3}){3})/is', $theIp, $ips)){
                        foreach($ips[1] as $ip){
                            if(!self::_isPersist($ip)){
                                $found = TRUE;
                                break;
                            }
                        }
                    }
                    if($found){
                        break;
                    }
                }
            }
        }
        if($number){
            return self::ip2long($ip);
        }
        return $ip;
    }

    /**
     * 判断是否是内网ip
     * @param string $ip
     * @return boolean
     */
    static private function _isPersist($ip)
    {
        $ip = self::ip2long($ip);
        $persist = explode(",", "167772160,184549375,2130706433,2130706433,2886729728,2887778303,3232235520,3232301055");
        $len = count($persist);
        for($i = 0; $i < $len; $i += 2){
            if($ip >= $persist[$i] && $ip <= $persist[$i + 1]){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 将IP转换成整型字符串
     * @param string $ip
     * @return int
     */
    static public function ip2long($ip)
    {
        if($ip && !is_int($ip)){
            $ip = sprintf("%u", ip2long($ip));
        }
        return $ip;
    }

    /**
     * 计算字符串长度
     * @param string $string
     * @param int $len
     * @param array $skip
     * @return int
     */
    static public function zlen($string, $len = 1, $skip = [], $limit = [])
    {
        if($skip){
            $string = str_replace($skip, '', $string);
        }
        switch($len){
            case 1:
                $strLength = mb_strlen($string, 'UTF8');
                break;
            case 2:
                $strLength = (strlen($string) + mb_strlen($string, 'UTF8')) / 2;
                break;
            case 3:
                $strLength = strlen($string);
                break;
            default:
                $mark = str_repeat("*", max(intval($len), 1));
                $string = preg_replace('/[\x{4e00}-\x{9fa5}]/uis', $mark, $string);
                $strLength = strlen($string);
        }
        return $strLength;
    }

    /**
     * 过滤id字符串/数组
     * @param $ids
     * @param null $min
     * @param bool|TRUE $unique
     * @return array
     */
    static public function filterIds($ids, $min = NULL, $unique = TRUE)
    {
        if(!is_array($ids) && !is_object($ids)){
            $ids = explode(',', $ids);
        }
        $data = array();
        $minLimit = is_null($min);

        foreach($ids as $id){
            $id = intval($id);
            if($minLimit || $id > $min){
                $data[] = $id;
            }
        }
        return $unique ? array_unique($data) : $data;
    }

    /**
     * 截取字符串
     * @param string $string - 源字符串
     * @param int $len - 截取的长度
     * @param int $start - 截取的起始点，暂不启用
     * @param string $charset - 字符串编码
     * @return string
     */
    static public function cut($string, $len, $start = 0, $charset = 'utf-8')
    {
        $charset = strtolower($charset);
        if($charset != 'utf-8'){
            $string = iconv($charset, 'utf-8', $string);
        }
        $chars = array(0x80, 0xe0, 0xf0, 0xf8, 0xfc, 0xff);
        $i = 0;
        $lenth = strlen($string);
        if($lenth == 0){
            return '';
        }
        $clen = 0;
        while($i < $lenth){
            $ord = ord($string{$i});
            $t = 1;
            foreach($chars as $k => $v){
                if($ord < $v){
                    $t = $k + 1;
                    break;
                }
            }
            $i += $t;
            $clen += ($t > 1 ? 2 : 1);
            if($clen >= $len){
                break;
            }
        }
        $res = substr($string, $start, $i);
        if($charset != 'utf-8'){
            $res = iconv('utf-8', $charset, $res);
        }
        return $res;
    }

    /**
     * @param $url
     * @param int $code
     */
    static public function redirect($url, $code = 200)
    {
        if($code){
            ob_end_clean();
            if($code == 301){
                header('HTTP/1.1 301 Moved Permanently');
            }
            header("location: {$url}");
        }
        exit();
    }

    /**
     * 分割换行
     * @param $string
     * @return mixed
     */
    static public function splitLine($string)
    {
        if($string){
            $string = preg_replace('#([\x{3002}\x{ff1b}\x{ff1f};\?\s]+)(?=<br>|<br/>)?(\d{1,2})#u', '$1<br/>$2', trim($string));
        }
        return $string;
    }

    /**
     * 换行转<br/>
     * @param $string
     * @return string
     */
    static public function nl2br($string, $replaceEmptyLine = false)
    {
        if($string){
            $string = str_replace(array("\r\n", "\r", "\n"), '<br/>', $string);
            $string = $replaceEmptyLine ? preg_replace('#(<br>|<br/>){2,}#is', '$1$1', $string) : $string;    // 多个空行只允许展示一行
        }
        return $string;
    }

    /**
     * @param $string
     * @return mixed
     */
    static public function stripBTag($string)
    {
        if($string){
            $string = str_replace(array("<b>", "</b>", "<u>", "</u>"), '', $string);
        }
        return $string;
    }

    /**
     * 字符串转义处理
     * @param mixed $data
     * @param int $op
     * @return mixed
     */
    static public function slashes($data, $op = 0)
    {
        if(is_array($data) || is_object($data)){
            foreach($data as $k => $v){
                $data[$k] = self::slashes($v, $op);
            }
        } else{
            if($op == 1){
                $data = addslashes($data);
            } elseif($op == -1){
                $data = stripslashes($data);
            } else{
                $data = addslashes(stripslashes($data));
            }
        }
        return $data;
    }

    /**
     * @param      $str
     * @param bool $face
     * @return mixed|string
     */
    static public function removeXss($str, $face = TRUE)
    {
        // 表情处理
        if($face){
            $patterns = "/<img src=\"e([\da-f]{3})\">/i";
            $replace = "[:\${1}]";
            $str = preg_replace($patterns, $replace, $str);
        }

        $str = self::space2htmlEntities($str);
        $str = self::xssAndSqlClean($str);
        $str = htmlspecialchars($str);
        $str = str_replace('&amp;nbsp;', '&nbsp;', $str);
        $str = str_replace('&lt;br&gt;', '<br>', $str);
        $str = str_replace('&lt;br/&gt;', '<br/>', $str);
        return $str;
    }

    /**
     * 产生随机字符串
     *
     * 产生一个指定长度的随机字符串,并返回给用户，可以用于生成密码
     *
     * @access public
     * @param int $len 产生字符串的位数
     * @param bool $specialChar
     * @return string
     */
    static public function randString($len = 6, $specialChar = TRUE)
    {
        $chars = 'ABDEFGHJKLMNPQRSTVWXYabdefghijkmnpqrstvwxy23456789';
        if($specialChar){
            $chars .= '#%*';
        }
        mt_srand((double)microtime() * 1000000 * getmypid());
        $str = '';
        while(strlen($str) < $len)
            $str .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $str;
    }

    /**
     * 从...之前
     * @param $time
     * @param null $format 时间格式
     * @param null $zero
     * @return bool|null|string
     */
    static public function since($time, $format = NULL, $zero = NULL)
    {
        if(is_null($format)){
            $format = 'Y-m-d';
        }
        $now = time();
        $since = $now - $time;
        if($time <= 0 && $zero){
            return $zero;
        }
        if($since >= 31 * 86400){
            return date($format, $time);
        } elseif($since >= 86400 && $since < 31 * 86400){
            return intval($since / 86400) . '天前';
        } elseif($since >= 3600){
            return intval($since / 3600) . '小时前';
        } elseif($since >= 60){
            return intval($since / 60) . '分钟前';
        } elseif($since >= 0){
            return $since . '秒前';
        }
        return date($format, $time);
    }

    /**
     * 从...之后
     * @param $time
     * @param null $format 时间格式
     * @param null $zero
     * @return bool|null|string
     */
    static public function then($time, $format = NULL, $zero = NULL)
    {
        if(is_null($format)){
            $format = 'Y-m-d';
        }
        $now = time();
        $then = $time - $now;
        if($time <= 0 && $zero){
            return $zero;
        }
        if($then >= 31 * 86400){
            return date($format, $time);
        } elseif($then >= 86400 && $then < 31 * 86400){
            return intval($then / 86400) . '天后';
        } elseif($then >= 3600){
            return intval($then / 3600) . '小时后';
        } elseif($then >= 60){
            return intval($then / 60) . '分钟后';
        } elseif($then >= 0){
            return $then . '秒后';
        }
        return date($format, $time);
    }

    /**
     * @param array $arr
     * @param string $key
     * @param bool $keyOnly
     * @return array
     */
    static public function mkKey($arr, $key = 'id', $keyOnly = FALSE)
    {
        $data = array();
        if(!is_array($arr)){
            return $data;
        }
        foreach($arr as $v){
            if(!isset($v[$key])){
                continue;
            }
            if($keyOnly){
                $data[] = $v[$key];
            } else{
                $data[$v[$key]] = $v;
            }
        }
        return $data;
    }

    /**
     * 验证数组是否存在键值为空的情况
     * @param $data
     * @param array $res 为空 - 验证通过 ，不为空 - 验证不通过
     * @param array $skipFields 不检查字段
     * @param int $depth 验证的深度 -1表示无限深度
     * @return bool
     */
    static public function validEmptyData($data, array &$res = [], array $skipFields = array(), &$depth = -1)
    {
        if(is_array($data)){
            foreach($data as $k => $v){
                if(is_array($v)){
                    if($depth == -1){
                        self::validEmptyData($v, $res, $skipFields);
                    }elseif($depth > 0){
                        $depth--;
                        self::validEmptyData($v, $res, $skipFields, $depth);
                    }
                } else{
                    if($skipFields){
                        if(empty($v) && !in_array($k, $skipFields)){
                            $res[] = $k;
                        }
                    } elseif(empty($v)){
                        $res[] = $k;
                    }
                }
            }
        } else{
            if(empty($v) && !in_array($data, $skipFields)){
                return $res[] = $data;
            }
        }
        return true;
    }

    /**
     * 字节转换字符串
     * @param $byte
     * @param int $precision
     * @return string
     */
    public static function byte2String($byte, $precision = 2)
    {
        if($byte < 1024){
            return $byte . 'B';
        } elseif(($size = round($byte / 1024, $precision)) < 1024){
            return $size . 'KB';
        } elseif(($size = round($byte / (1024 * 1024), $precision)) < 1024){
            return $size . 'MB';
        } else{
            return round($byte / (1024 * 1024 * 1024), $precision) . 'GB';
        }
    }

    /**
     * 获取文件后缀名
     * @param $fileName
     * @return string
     */
    public static function getSuffix($fileName)
    {
        $suffix = '';
        if($fileName){
            $pos = strrpos($fileName, '.');
            $suffix = substr($fileName, $pos, mb_strlen($fileName, 'utf-8') - 1);
        }
        return $suffix;
    }

    /**
     * @param $string
     * @return int
     */
    static public function string2bytes($string)
    {
        if(preg_match('/^(\d+)(k|b|m|g|t|p|)/is', $string, $res)){
            $size = $res[1];
            $res[2] = strtolower($res[2]) ?: 'b';
            $offset = ['k' => 10, 'm' => 20, 'g' => 30, 't' => 40, 'p' => 50];
            if(isset($offset[$res[2]])){
                $size <<= $offset[$res[2]];
            }
        } else{
            $size = intval($string);
        }
        return intval($size);
    }

    /**
     * @param $string
     * @param bool $all 全中文验证
     * @return bool|int
     */
    static public function isChinese($string, $all = true)
    {
        $pattern = $all ? '/^[\x{4e00}-\x{9fa5}]{0,}$/uis' : '/[\x{4e00}-\x{9fa5}]{2,15}/uis';
        if($string){
            return preg_match($pattern, $string);
        }
        return false;
    }

    /**
     * html换行标签转换行符
     * @param $string
     * @return mixed
     */
    static public function br2nl($string)
    {
        if($string){
            $string = preg_replace('/<br\s*?\/??>/is', PHP_EOL, $string);
        }
        return $string;
    }

    /**
     * xss和sql过滤
     * @param $data
     * @return mixed|string
     */
    static public function xssAndSqlClean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do{// Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while($old_data !== $data);
        // we are done...
        return $data;
    }

    /**
     * @param $string
     * @return mixed
     */
    static public function space2htmlEntities($string)
    {
        if($string){
            $string = str_replace(' ', '&nbsp;', $string);
        }
        return $string;
    }
}