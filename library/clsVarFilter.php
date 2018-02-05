<?php


class clsVarFilter
{
    /**
     * @var mixed|null
     */
    private $_value = NULL;

    public function __construct($value = NULL)
    {
        $this->_value = $value;
    }

    /**
     * @param int      $default
     * @param int|null $min
     * @param int|null $max
     * @return int|null
     */
    public function toInt($default = 0, $min = NULL, $max = NULL)
    {
        if(is_null($this->_value)){
            return $default;
        }
        $value = intval($this->_value);
        if($max !== NULL && $value > $max){
            $value = $max;
        }
        if($min !== NULL && $value < $min){
            $value = $min;
        }
        return $value;
    }

    /**
     * @param bool   $trim
     * @param string $default
     * @param null   $length
     * @param null   $start
     * @return mixed|null|string
     */
    public function toString($trim = FALSE, $default = '', $length = NULL, $start = NULL)
    {
        if(is_null($this->_value)){
            return $default;
        }
        if($trim){
            $value = $trim === TRUE ? trim($this->_value) : trim($this->_value, $trim);
        } else{
            $value = $this->_value;
        }
        if($length === NULL && $start === NULL){
            return $value;
        } elseif($start === NULL){
            return substr($value, 0, $length);
        } elseif($length === NULL){
            return substr($value, $start);
        } else{
            return substr($value, $start, $length);
        }
    }

    /**
     * @param float $default
     * @param null  $min
     * @param null  $max
     * @return float|null
     */
    public function toFloat($default = 0.0, $min = NULL, $max = NULL)
    {
        if(is_null($this->_value)){
            return $default;
        }
        $value = floatval($this->_value);
        if($max !== NULL && $value > $max){
            $value = $max;
        }
        if($min !== NULL && $value < $min){
            $value = $min;
        }
        return $value;
    }

    /**
     * @param array $default
     * @return array
     */
    public function toArray($default = array())
    {
        if(is_null($this->_value)){
            return $default;
        }
        return (array)$this->_value;
    }

    /**
     * @return object
     */
    public function toObject()
    {
        return (object)$this->_value;
    }

    /**
     * @param bool $default
     * @return bool
     */
    public function toBoolean($default = FALSE)
    {
        return is_null($this->_value) ? $default : ($this->_value ? TRUE : FALSE);
    }

    /**
     * @return mixed|null
     */
    public function value()
    {
        return $this->_value;
    }

    /**
     * @param mixed $search
     * @param mixed $replace
     * @return mixed
     */
    public function replace($search, $replace)
    {
        if(is_callable($replace)){
            return preg_replace_callback($search, $replace, $this->_value);
        } else{
            return preg_replace($search, $replace, $this->_value);
        }
    }

    /**
     * @param mixed $search
     * @param mixed $replace
     * @return mixed
     */
    public function sreplace($search, $replace)
    {
        return str_replace($search, $replace, $this->_value);
    }

    /**
     * @param string $regx
     * @param array  $res
     * @return int
     */
    public function find($regx, &$res = NULL)
    {
        return preg_match($regx, $this->_value, $res);
    }

    /**
     * @param string $regx
     * @param array  $res
     * @return int
     */
    public function findAll($regx, &$res = NULL)
    {
        return preg_match_all($regx, $this->_value, $res);
    }

    /**
     * @param string $str
     * @param bool   $revert
     * @return int
     */
    public function indexOf($str, $revert = FALSE)
    {
        if($revert){
            $pos = strrpos($this->_value, $str);
        } else{
            $pos = strpos($this->_value, $str);
        }
        return $pos === FALSE ? -1 : $pos;
    }

    /**
     * @param int $type
     * @return mixed
     */
    public function slashes($type = 0)
    {
        return clsTools::slashes($this->_value, $type);
    }

    /**
     * @param      $split
     * @param null $limit
     * @return array
     */
    public function split($split, $limit = NULL)
    {
        return $limit ? explode($split ?: ',', $this->_value, $limit) : explode($split ?: ',', $this->_value);
    }

    public function toIds($split = NULL, $min = NULL, $max = NULL)
    {
        $arr = $this->split($split);
        $result = array();
        foreach($arr as $val){
            $val = intval($val);
            if(($min === NULL || $val >= $min) && ($max === NULL || $val <= $max)){
                $result[] = $val;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return var_export($this->_value, 1);
    }

    /**
     * 判断参数是否为null
     * @return bool
     */
    public function isNull()
    {
        return is_null($this->_value);
    }
}