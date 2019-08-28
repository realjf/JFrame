<?php


class clsDecrypt
{
    private $key = ""; // 秘钥
    private $iv = ""; // 向量

    public static $block_size = 32;

    public function __construct($key = "", $iv = "")
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * pkcs7补码
     * @param string $string  明文
     * @param int $blocksize Blocksize , 以 byte 为单位
     * @return String
     */
    private function addPkcs7Padding($string, $blocksize = 32) {
        $len = strlen($string); //取得字符串长度
        $pad = $blocksize - ($len % $blocksize); //取得补码的长度
        $string .= str_repeat(chr($pad), $pad); //用ASCII码为补码长度的字符， 补足最后一段
        return $string;
    }

    /**
     * 解密
     * @param $encryptedText
     * @return String
     */
    public function aes128cbcDecrypt($encryptedText) {
        return $this->stripPkcs7Padding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $encryptedText, MCRYPT_MODE_CBC, $this->iv));
    }

    /**
     * 除去pkcs7 padding
     *
     * @param String 解密后的结果
     *
     * @return String
     */
    private function stripPkcs7Padding($string)
    {
        $pad = ord(substr($string, -1));
        if ($pad < 1 || $pad > self::$block_size)
            $pad = 0;
        return substr($string, 0, (strlen($string) - $pad));

//        $pad = ord($string[strlen($string) - 1]);
//        if ($pad > strlen($string))
//            return false;
//        if (strspn($string, chr($pad), strlen($string) - $pad) != $pad)
//            return false;
//        return substr($string, 0, -1 * $pad);
    }

    function hexToStr($hex)//十六进制转字符串
    {
        $string="";
        for($i=0;$i<strlen($hex)-1;$i+=2)
            $string.=chr(hexdec($hex[$i].$hex[$i+1]));
        return  $string;
    }

    function strToHex($string)//字符串转十六进制
    {
        $hex="";
        $tmp="";
        for($i=0;$i<strlen($string);$i++)
        {
            $tmp = dechex(ord($string[$i]));
            $hex.= strlen($tmp) == 1 ? "0".$tmp : $tmp;
        }
        $hex=strtoupper($hex);
        return $hex;
    }

    function aes128cbcEncrypt($str) {    // $this->addPkcs7Padding($str,16)
        $base = (mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $this->addPkcs7Padding($str,16) , MCRYPT_MODE_CBC, $this->iv));
        return $this->strToHex($base);
    }
}