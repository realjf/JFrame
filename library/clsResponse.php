<?php


class clsResponse {

    public function __construct() {

    }

    public function callback($datas) {
        if (!isset($_REQUEST['callback']) || empty($_REQUEST['callback'])) {
            return FALSE;
        }

        $callback = $_REQUEST['callback'];
        if (!preg_match("/^[_a-zA-Z0-9.]+$/", $callback)) {
            return FALSE;
        }

        $jsonString = json_encode($datas, JSON_UNESCAPED_UNICODE);
        echo $callback.'('.$jsonString.');';
        return TRUE;
    }

    public function response($code, $result = NULL, $msg = '', $contentType = 'application/json') {
        $o          = new \stdClass();
        $o->code    = $code;
        $o->result  = $result;
        $o->msg     = $msg;
        header('Content-Type:' . $contentType . ';charset=utf-8');
        if (!$this->callback($o)) {
            $jsonString = json_encode($o, JSON_UNESCAPED_UNICODE);
            echo $jsonString;
        }
        exit;
    }

}