<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/23
 * Time: 18:28
 */

namespace Module\Admin;


class ctlRecruit extends ctlBase
{
    /**
     * 简历进程
     */
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $cond = $this->_getSearchCond();
        $data = mdlResumeOp::instance()->getDeliverLogList($cond, $page, $this->defaultPerPage);
        $jobs = $users = $resumeAttaches = [];
        if($data['data']){
            $jobIds = \clsTools::mkKey($data['data'], 'job_id', true);
            $jobs = mdlJob::instance()->getJobNameById($jobIds);
            $uids = \clsTools::mkKey($data['data'], 'uid', true);
            $users = mdlResumeOp::instance()->getResumeByUids($uids);
            $resumeAttaches = mdlResumeFile::instance()->getByUid($uids);
        }
        // 获取跟进人
        $follows = mdlSysUser::instance()->getAllFollows();

        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $this->assign([
            'data' => $data['data'],
            'jobs' => $jobs,
            'users' => $users,
            'attaches' => $resumeAttaches,
            'follows' => $follows,
            'categories' => \defConst::$_category,
            'applyStatus' => \defConst::$auditProcessStatus,
            'statuses' => \defConst::$audit_status,
        ]);
        $this->display('recruit_index.html');
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
        $searchCond = [];
        if($cond){
            $jobs = mdlJob::instance()->fetchByCond($cond);
            $jobIds = \clsTools::mkKey($jobs, 'id', true);
            $searchCond = ['job_id in(' . implode(',', $jobIds) . ')'];
        }
        // 过滤出进程中简历
        if(isset($search['status'])){
            if($search['status'] == 0 || $search['status'] == 1){ // 0新简历和1待沟通做相同处理
                $searchCond[] = "status in(0,1)";
            }else{
                $searchCond['status'] = intval($search['status']);
            }
        }else{
            $searchCond[] = "status >=0 and status < 4";
        }

        $this->assign('searchCond', $search);
        return $searchCond;
    }

    public function funcOther()
    {
        $this->needLogin();
        $this->display('recruit_other.html');
    }

    /**
     * 简历预览
     */
    public function funcPreview()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        if($id) {
            $id = \clsVars::get('id')->toInt(0);
            $uid = \clsVars::get('uid')->toInt(0);
            if ($id && $uid) {
                $res = mdlResumeWebOP::instance()->getSnapshot($id, $uid);
                if ($res) {
                    $res['experience'] = json_decode($res['experience'], true);
                    $res['edu_backgnd'] = json_decode($res['edu_backgnd'], true);
                    $res['graduation_date'] = date('Y-m', $res['graduation_date']);
                }
                $resExt = mdlResumeWebOP::instance()->getSnapshotExt($id, $uid);
                if ($resExt && $resExt['job_id']) {
                    $jobId = $resExt['job_id'];
                } else {
                    $jobIds = mdlResumeOP::instance()->getDeliverLogByUid($uid);
                    $jobId = "";
                    foreach ($jobIds as $v) {
                        if ($v['resume_id'] == $id) {
                            $jobId = $v['job_id'];
                        }
                    }
                }
                $jobs = $jobId ? mdlJob::instance()->getJobDetailById($jobId) : "";
                $resExt['job_name'] = $jobs ? $jobs['name'] : "";
                $res['major'] = $res['major'] ?: $resExt['major'];
                $other = mdlCustomFields::instance()->getByCond(['custom_type' => mdlCustomFields::CUSTOM_TYPE_SNAPSHOT, 'custom_id' => intval($id), 'type' => 'other']);
                $resExt['other'] = $other && $other['value'] ? $other['value'] : [];
                $family = mdlCustomFields::instance()->getByCond(['custom_type' => mdlCustomFields::CUSTOM_TYPE_SNAPSHOT, 'custom_id' => intval($id), 'type' => 'family']);
                $resExt['family'] = $family && $family['value'] ? $family['value'] : [];
                $this->assign([
                    'res' => $res,
                    'resExt' => $resExt,
                    'degrees' => \defConst::$_degree,
                    'sexes' => \defConst::$_sex,
                    'marriages' => \defConst::$_marriage,
                    'promotes' => \defConst::$_promotes,
                    'family' => \defConst::$_families,
                    'eduType' => \defConst::$_eduType,
                ]);
            }
        }
        $this->display('recruit_preview.html');
    }

    /**
     * 编辑操作
     */
    public function funcOp()
    {
        $this->needLogin();
        $action = \clsVars::get('action')->toString();
        $id = \clsVars::get('id')->toInt();
        if($action == 'pass'){
            // 审核通过操作
            $status = \clsVars::get('status')->toInt();
            $this->assign([
                'applyStatus' => \defConst::$audit_status,
                'action' => $action,
                'status' => $status,
                'id' => $id,
                'remarks' => \defConst::$_remarks,
            ]);
            $this->display('recruit_edit_pass.html');
        }elseif($action == 'reject'){
            // 审核拒绝
            $this->assign([
                'rejectStatus' => \defConst::$auditRejectStatus,
                'id' => $id,
                'action' => $action,
            ]);
            $this->display('recruit_edit_reject.html');
        }elseif($action == 'save'){
            $id = \clsVars::post('id')->toInt();
            $data['status'] = \clsVars::post('status')->toInt(0);
            $data['remarks'] = \clsVars::post('remarks')->toString();
            if(!$id){
                $this->output('101:缺少id');
            }
            if(!$data['status']){
                $this->output('101:请选择简历状态');
            }
            if(!$data['remarks']){
                $this->output('101:请填写简历信息反馈');
            }
            // 保存入库
            $data['remarks'] = \clsTools::nl2br($data['remarks']);
            $res = mdlResumeOP::instance()->updateDeliverLogById($id, $data);
            if($res !== FALSE){
                $this->output('100:保存成功');
            }else{
                $this->output('99:保存失败');
            }
        }
    }
}