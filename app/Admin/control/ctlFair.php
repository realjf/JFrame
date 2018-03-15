<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/15
 * Time: 11:54
 */

namespace Module\Admin;


class ctlFair extends ctlBase
{
    /**
     * 招聘会
     */
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlJobFair::instance()->getList(0, $page, $this->defaultPerPage);
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $this->assign([
            'data' => $data['data']
        ]);
        $this->display('job_fair.html');
    }

    /**
     * 招聘会 - 显示控制
     */
    public function funcShow()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        $status = \clsVars::get('status')->toInt();
        if(!$id){
            $this->output('101:缺少参数');
        }
        $res = mdlJobFair::instance()->setStatus($id, $status);
        if($res === FALSE){
            $this->output('99:操作失败');
        }
        $this->output('100:操作成功');
    }

    /**
     * 招聘会 - 编辑
     */
    public function funcEdit()
    {
        $action = \clsVars::get('action')->toString();
        if($action == 'save'){
            $id = \clsVars::post('id')->toInt();
            $data['title'] = \clsVars::post('title')->toString();
            $data['url'] = \clsVars::post('url')->toString();
            $data['pic_url'] = \clsVars::post('pic_url')->toString();
            $data['zone'] = \clsVars::post('zone')->toString();
            $data['place'] = \clsVars::post('place')->toString();
            $data['desc'] = \clsVars::post('desc')->toString();
            $data['sort'] = \clsVars::post('sort')->toInt();
            $data['type'] = \clsVars::post('type')->toInt();
            $data['show'] = \clsVars::post('show')->toInt();
            $data['stime'] = \clsVars::post('stime')->toString();
            $data['etime'] = \clsVars::post('etime')->toString();

            if((!$data['stime'] && $data['etime']) || ($data['stime'] && !$data['etime'])){
                $this->response(102, NULL, '缺少开始时间或结束时间');
            }
            if($data['stime'] && $data['etime'] && strtotime($data['stime']) >= strtotime($data['etime'])){
                $this->response(101, NULL, '结束时间应大于开始时间');
            }
            if(!$data['title']){
                $this->response(103, NULL, '缺少标题');
            }

            if(!$data['title'] || !$data['zone']){
                $this->output('101:缺少信息');
            }

            $res = mdlJobFair::instance()->save($id, $data);
            if($res === FALSE){
                $this->output('99:保存失败');
            }
            $this->output('100:保存成功');
        }else{
            $id = \clsVars::get('id')->toInt();
            if($id){
                $data = mdlJobFair::instance()->get($id);
                $this->assign('data', $data);
            }
            $this->assign([
                'types' => mdlJobFair::$_types,
            ]);
            $this->display('job_fair_edit.html');
        }
    }
}