<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/5
 * Time: 14:46
 */
namespace Module\Admin;



class ctlIndex extends ctlBase
{
    public function funcIndex()
    {
        $this->needLogin();
        $this->display('index.html');
    }
}