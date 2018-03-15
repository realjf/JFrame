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
        if($search['name']){
            $cond[] = "j_name like '%" . trim(\clsTools::slashes($search['name'])) . "%'";
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
}