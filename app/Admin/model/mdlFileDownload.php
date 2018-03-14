<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/5
 * Time: 13:40
 */

namespace Module\Admin;


class mdlFileDownload extends mdlBase
{
    /**
     * 后台文件管理目录
     * @var string
     */
    static public $_uploadPath = '/zhaopin/uploads/';

    /**
     * 上传文件目录
     * @var string
     */
    static public $_tmpUploadPath = '/tmp/uploads/';

    public function __construct()
    {
        if(!SYS_IS_ONLINE){
            self::$_uploadPath = '/zhaopin/t240/uploads/';
        }
    }

    /**
     * 文件下载输出
     * @param $showName
     * @param $filePath
     */
    static public function download($showName, $filePath)
    {
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; filename=\"" . urlencode(str_replace('/\s+/is', '_', $showName)) . "\"");
        header("Accept-Length:" . filesize($filePath));
        ob_start();
        echo readfile($filePath);
        $content = ob_get_contents();
        ob_get_clean();
        echo $content;
        exit();
    }

    static public function getFileName()
    {

    }

    /**
     * 文件移动
     * @param $sourceFile
     * @return bool
     */
    static public function moveTo($sourceFile)
    {
        $date = date('Ymd', time());
        $uploadPath = self::$_uploadPath . $date . '/';
        if(is_file($uploadPath . $sourceFile)){ // 文件是否已存在
            return TRUE;
        }
        $file = self::$_tmpUploadPath . $sourceFile;
        if(!file_exists($file) || !is_file($file)){
            return FALSE;
        }
        if(!file_exists($uploadPath) || !is_writable($uploadPath)){
            if(!@mkdir($uploadPath, 0777, true)){
                return FALSE;
            }
        }
        if(!@copy($file, $uploadPath . $sourceFile)){
            return FALSE;
        }
        unlink($file);
        return TRUE;
    }

    /**
     * @param $file
     * @return float
     */
    static public function getFileSize($file)
    {
        $date = date('Ymd', time());
        $uploadPath = self::$_uploadPath . $date . '/';
        $file = $uploadPath . $file;
        $size = filesize($file);
        if($size < 0){
            $size = 0;
        }
        // byte转换成kb
        $size = round($size/1024, 2);
        return floatval($size);
    }
}