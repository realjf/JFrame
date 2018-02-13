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
        $id = \clsVars::get('id')->toInt();
        $this->display('index.html', ['title' => 'hello world']);
    }
}