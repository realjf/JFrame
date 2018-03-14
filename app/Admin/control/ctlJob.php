<?php
/**
 * 发布系统
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/23
 * Time: 16:33
 */

namespace Module\Admin;


class ctlJob extends ctlBase
{
    /**
     * 职位管理
     */
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $cond = $this->_getSearchCond();
        $data = mdlJob::instance()->getList($cond, $page, $this->defaultPerPage);
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        // 分成两组
        $data1 = $data2 = [];
        if($data['data']){
            $splice = min(count($data['data']), $this->defaultPerPage) / 2;
            $data1 = array_slice($data['data'], 0, $splice);
            $data2 = array_slice($data['data'], $splice);
        }
        // 获取跟进人
        $follows = mdlSysUser::instance()->getAllFollows();
        $this->assign([
            'data1' => $data1,
            'data2' => $data2,
            'categories' => \defConst::$_category,
            'follows' => $follows,
        ]);
        $this->display('job_index.html');
    }

    /**
     * @return array
     */
    private function _getSearchCond()
    {
        $cond = [];
        $search = \clsVars::get('search')->value();
        if($search['follow']){
            $cond['j_follow'] = intval($search['follow']);
        }
        if($search['category']){
            $cond['j_category'] = intval($search['category']);
        }
        if($search['pub_status']){
            $cond['j_pub_status'] = intval($search['pub_status']);
        }
        $this->assign('searchCond', $search);
        return $cond;
    }

    /**
     * 发布新职位
     */
    public function funcEdit()
    {
        $this->needLogin();
        $action = \clsVars::get('action')->toString(true);
        $id = \clsVars::get('id')->toInt();
        if($action == 'save'){
            // 保存操作
            $id = \clsVars::post('id')->toInt();
            $data['j_follow'] = \clsVars::post('follow')->toString();
            $data['j_name'] = \clsVars::post('name')->toString();
            $data['j_number'] = \clsVars::post('number')->toInt();
            $data['j_school'] = \clsVars::post('isschool')->toInt();
            $data['j_duty'] = \clsVars::post('duty')->toString(false);
            $data['j_place'] = \clsVars::post('place')->toString();
            $data['j_category'] = \clsVars::post('category')->toInt();
            if(!$data['j_name'] || !$data['j_number'] || !$data['j_duty'] || !$data['j_place'] || !$data['j_category']){
                $this->output('101:缺少信息');
            }
            $res = mdlJob::instance()->updateJobById($id, $data);
            if($res === FALSE){
                $this->output('99:保存失败');
            }
            $this->output('100:保存成功');
        }else{
            if($id){
                $data = mdlJob::instance()->getJobDetailById($id);
                $this->assign('data', $data);
            }
            // 获取跟进人
            $follows = mdlSysUser::instance()->getAllFollows();
            $categories = \defConst::$_category;
            unset($categories[0]);
            $this->assign([
                'categories' => $categories,
                'follows' => $follows,
            ]);
            $this->display('job_edit.html');
        }
    }

    /**
     * 职位管理 - 操作
     */
    public function funcOp()
    {
        $this->needLogin();
        $action = \clsVars::get('action')->toString();
        if($action == 'publish'){
            // 发布操作
            $id = \clsVars::get('id')->toInt();
            $status = \clsVars::get('status')->toInt();
            if(!$id || !$status){
                $this->output('101:缺少参数');
            }
            $res = mdlJob::instance()->setPubStatus($id, $status);
            if($res !== FALSE){
                $this->output('100:操作成功');
            }
            $this->output('99:操作失败');
        }elseif($action == 'edit'){
            // 编辑操作
            $id = \clsVars::get('id')->toInt();
            if($id){
                $data = mdlJob::instance()->getJobDetailById($id);
                $this->assign('data', $data);
            }
            $this->display('job_post.html');
        }else{
            $this->output('99:操作失败');
        }
    }

    /**
     * 招聘会
     */
    public function funcFair()
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
    public function funcFairEdit()
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