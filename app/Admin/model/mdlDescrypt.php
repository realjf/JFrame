<?php


namespace Module\Admin;


class mdlDescrypt extends mdlBase
{
    /**
     * 长度8 密钥
     * @var string
     */
    static private $_salt = '3@j*l&8#';

    /**
     * 加密字符串
     * @param $string
     * @return string
     */
    static public function encrypt($string)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);

        $encryptString = mcrypt_encrypt(MCRYPT_DES, self::$_salt, $string, MCRYPT_MODE_ECB, $iv);

        $encode = base64_encode($encryptString);

        return $encode;
    }

    /**
     * 解密字符串
     * @param $string
     * @return string
     */
    static public function descrypt($string)
    {
        $decode = base64_decode($string);

        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);

        $descryptString = mcrypt_decrypt(MCRYPT_DES, self::$_salt, $decode, MCRYPT_MODE_ECB, $iv);

        return trim($descryptString);
    }
}