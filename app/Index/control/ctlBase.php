<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2017/11/21
 * Time: 15:43
 */

namespace Module\Index;


use Core\JFrame\Template;

class ctlBase extends Template
{
    public function display($tpl, $data, $tplDir = '')
    {
        $tplDir = $tplDir ?: __DIR__ . '/../template';
        parent::display($tpl, $data, $tplDir);
    }
}