<?php
/**
 * 生成二维码图片
 * Class QrCodeSdk
 */


namespace Library\Phpqrcode;

include  dirname(__FILE__).DIRECTORY_SEPARATOR . "qrlib.php";

class QrCodeSdk
{
    /**
     * @param string $text 二维码内容
     * @param bool $outfile  生成二维码文件名，false为不保存
     * @param int $level  级别，也是容错率
     *      QR_ECLEVEL_L,    最大 7% 的错误能够被纠正
     *      QR_ECLEVEL_M,  最大 15% 的错误能够被纠正；
     *      QR_ECLEVEL_Q,   最大 25% 的错误能够被纠正；
     *      QR_ECLEVEL_H,   最大 30% 的错误能够被纠正；
     * @param int $size 大小
     * @param int $margin 外边距
     * @param bool $saveandprint 是否保存和打印
     */
    static public function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 5, $margin = 4, $saveandprint = false)
    {
        return \QRcode::png($text, $outfile, $level, $size, $margin, $saveandprint);
    }
}