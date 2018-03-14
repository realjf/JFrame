<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/3/14
 * Time: 18:16
 */

namespace Module\Admin;


class ctlResume extends ctlBase
{
    /**
     * 简历库
     */
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $cond = $this->_getFilter();
        $data = mdlResumeOp::instance()->getDeliverLogList($cond, $page, $this->defaultPerPage);
        $jobs = $users = $resumeAttaches = [];
        if($data['data']){
            $rIds = \clsTools::mkKey($data['data'], 'job_id', true);
            $jobs = mdlJob::instance()->getJobNameById($rIds);
            $uids = \clsTools::mkKey($data['data'], 'uid', true);
            $users = mdlResumeOp::instance()->getResumeByUids($uids);
            $resumeAttaches = mdlResumeFile::instance()->getByUid($uids);
        }
        // 获取跟进人
        $follows = mdlSysUser::instance()->getAllFollows();
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $categories = [];
        foreach (\defConst::$_category as $key => $item) {
            if($key == 0){
                continue;
            }
            $categories[] = [
                'id' => $key,
                'text' => $item,
            ];
        }
        $audit_status = [];
        $applyStatus = \defConst::$audit_status;
        unset($applyStatus[0]);
        foreach ($applyStatus as $key => $item){
            $audit_status[] = [
                'id' => $key,
                'text' => $item,
            ];
        }

        $this->assign([
            'applyStatus' => \defConst::$audit_status,
            'data' => $data['data'],
            'jobs' => $jobs,
            'users' => $users,
            'attaches' => $resumeAttaches,
            'follows' => $follows,
            'categories' => $categories ? json_encode($categories, JSON_UNESCAPED_UNICODE) : '',
            'status'    => $audit_status ? json_encode($audit_status, JSON_UNESCAPED_UNICODE) : '',
        ]);
        $this->display('resume_index.html');
    }

    /**
     * 获取简历库搜索条件
     * @return array
     */
    private function _getFilter()
    {
        $cond = [];
        $search = \clsVars::get('search')->value();
        if($search['follow']){
            $cond['j_follow'] = intval($search['follow']);
        }
        $oldCategory = [];
        if($search['category']){
            $cond['j_category'] = intval($search['category']);
            $oldCategory[] = intval($search['category']);
        }
        $this->assign('oldCategory', json_encode($oldCategory, JSON_UNESCAPED_UNICODE));
        $searchCond = [];
        if($cond){
            $jobs = mdlJob::instance()->fetchByCond($cond);
            $jobIds = \clsTools::mkKey($jobs, 'id', true);
            $searchCond = ['job_id in(' . implode(',', $jobIds) . ')'];
        }
        if($search['stime']){
            $search['stime'] = strtotime($search['stime']);
            $searchCond[] = "dateline>=" .  $search['stime'];
        }
        if($search['etime']){
            $search['etime'] = strtotime($search['etime']);
            $searchCond[] = "dateline <= " . $search['etime'];
        }
        $oldStatus = [];
        if($search['status']){
            $status = \clsTools::filterIds($search['status']);
            $searchCond[] = "status in(" . implode(',', $status) . ")";
            // 返回旧数据
            foreach ($status as $key){
                $oldStatus[] = intval($key);
            }
        }
        $this->assign('oldStatus', json_encode($oldStatus, JSON_UNESCAPED_UNICODE));
        $this->assign('searchCond', $search);
        return $searchCond;
    }

    /**
     * 编辑
     */
    public function funcEdit()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        $status = \clsVars::get('status')->toInt();
        if($id){
            $data = mdlResumeOP::instance()->getDeliverLogById($id);
            if(!$data['remarks']){
                $status = intval($data['status']);
                $data['remarks'] = \defConst::$_remarks[$status];
            }
            $this->assign('data', $data);
        }
        $applyStatus = \defConst::$audit_status;
        unset($applyStatus[0]);
        $remarks = \defConst::$_remarks;
        unset($remarks[0]);
        $this->assign([
            'id' => $id,
            'applyStatus' => $applyStatus,
            'status' => $status,
            'remarks' => json_encode($remarks, JSON_UNESCAPED_UNICODE),
            ''
        ]);
        $this->display('resume_edit.html');
    }

    public function funcDelete()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        if(!$id){
            $this->output('101:缺少参数');
        }


    }
}