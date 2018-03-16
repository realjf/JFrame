<?php

namespace Module\Admin;


class ctlUser extends ctlBase
{
    /**
     * 注册用户
     */
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlUser::instance()->getList([], $page, $this->defaultPerPage);
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $this->assign([
            'data' => $data['data']
        ]);
        $this->display('user_list.html');
    }

    /**
     * 用户拉黑操作
     */
    public function funcBlack()
    {
        $this->needLogin();
        $uid = \clsVars::request('uid')->toInt();
        $status = \clsVars::request('status')->toInt();
        if(!$uid){
            $this->output('101:缺少uid');
        }
        $res = mdlUser::instance()->setStatus($uid, $status);
        if($res === FALSE){
            $this->output('99:操作失败');
        }
        $this->output('100:操作成功');
    }

    /**
     * 管理员
     */
    public function funcManager()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlSysUser::instance()->getList([], $page, $this->defaultPerPage);
        if($data['data']){
            foreach ($data['data'] as $k => $v){
                $v['last_login_ip'] = $v['last_login_ip'] ? long2ip($v['last_login_ip']) : '';
                $v['last_login_time'] = $v['last_login_time'] ? date('Y-m-d H:i:s', $v['last_login_time']) : '';
                $v['ctime'] = $v['ctime'] ? date('Y-m-d H:i:s', $v['ctime']) : '';
                $data['data'][$k] = $v;
            }
        }
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $this->assign([
            'data' => $data['data'],
            'isSuper' => $this->__curMember['type'] ? 1 : 0,
            'uid' => $this->__curMember['id'] ?: 0,
        ]);
        $this->display("user_manager.html");
    }

    /**
     * 管理员操作
     */
    public function funcOp()
    {
        $this->needLogin();
        $id = \clsVars::request('id')->toInt();
        $status = \clsVars::request('status')->toInt();
        if(!$id){
            $this->output('101:缺少参数');
        }
        $res = mdlSysUser::instance()->setStatus($id, $status);
        if($res === FALSE){
            $this->output('99:操作失败');
        }
        $this->output('100:操作成功');
    }

    /**
     * 管理员 - 编辑
     */
    public function funcEdit()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        if($id){
            $data = mdlSysUser::instance()->get($id);
            $this->assign('data', $data);
        }
        $this->display('user_manager_edit.html');
    }

    /**
     * 管理员 - 信息修改
     */
    public function funcSave()
    {
        $this->needLogin();
        $id = \clsVars::post('id')->toInt();
        $name = \clsVars::post('name')->toString(true);
        $email = \clsVars::post('email')->toString(true);
        $oldName = \clsVars::post('old_name')->toString(true);
        $opassword = \clsVars::post('opassword')->toString(true);
        $password = \clsVars::post('npassword')->toString(true);
        $rpassword = \clsVars::post('rpassword')->toString(true);
        if(!$name || !$email){
            $this->response('101:缺少信息');
        }
        if($id){
            // 4. 保存
            $res = mdlLogin::instance()->updateUserInfo($id, $name, $email, $opassword, $password, $rpassword);
            // 注销
            mdlLogin::instance()->session('reset');
        }else{
            // 新增
            if(!$name || !$password || !$rpassword || !$email){
                $this->response('101:必填项为空');
            }
            $res = mdlLogin::instance()->register($name, $email, $password, $rpassword);
        }
        $this->output($res);
    }

    /**
     * 管理员登录日志
     */
    public function funcLog()
    {
        $this->needLogin();
    }
}