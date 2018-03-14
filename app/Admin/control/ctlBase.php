<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/5
 * Time: 15:46
 */

namespace Module\Admin;


use Core\JFrame\Template;

class ctlBase extends Template
{
    protected $__curMember = [];

    /**
     * 页面title
     * @var array
     */
    private static $_pageTitles = [
        'user-index'  => '用户管理 - 注册用户',
        'user-manager' => '用户管理 - 管理员',
        'job-index'       => '发布系统 - 职位管理',
        'job-post'      => '发布系统 - 发布新职位',
        'job-fair'      => '发布系统 - 招聘会',
        'recruit-index' => '招聘系统 - 简历进程',
        'resume-index' => '招聘系统 - 简历库',
        'recruit-other'  => '招聘系统 - 其他渠道进度反馈',
        'file-index'    => '文件管理'
    ];

    public function funcIndex()
    {

    }

    protected function redirect($url, $code = 200)
    {
        \clsTools::redirect($url, $code);
    }

    public function display($tpl, $data = [], $tplDir = '')
    {
        $user = mdlLogin::instance()->session('get');
        $pageTitle = $this->__options['control'] . '-' . $this->__options['method'];
        $data['pageTitle'] = isset(self::$_pageTitles[$pageTitle]) ? self::$_pageTitles[$pageTitle] : '未知';
        $data['username'] = $user && isset($user['name']) ? $user['name'] : '';
        parent::display($tpl, $data, $tplDir);
    }

    /**
     * 需要登录
     */
    protected function needLogin()
    {
        $user = mdlLogin::instance()->session('get');
        if($user && isset($user['id']) && $user['id'] > 0){
            $this->__curMember = $user;
        }else{
            $this->redirect('/admin/login-index.html');
        }
    }
}