<?php


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
        $jobs = $users = $resumeAttaches = $jobCategory = [];
        if($data['data']){
            $jobIds = \clsTools::mkKey($data['data'], 'job_id', true);
            $jobs = mdlJob::instance()->getJobNameById($jobIds);
            $jobCategory = mdlJob::instance()->getJobCategoryByIds($jobIds);
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
            'jobCategory' => $jobCategory,
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
        if($search['name']){
            $cond[] = "j_name like '%" . trim(\clsTools::slashes($search['name'])) . "%'";
        }
        $searchCond = [];
        if($cond){
            $jobs = mdlJob::instance()->fetchByCond($cond);
            $jobIds = \clsTools::mkKey($jobs, 'id', true);
            $searchCond = ['job_id in(' . implode(',', $jobIds) . ')'];
        }
        // 用户名搜索
        if($search['username']){
            $users = mdlResumeOp::instance()->fetchByName(\clsTools::slashes($search['username']));
            $uids = \clsTools::mkKey($users, 'uid', true);
            $searchCond[] = "uid in(" . implode(',', $uids) . ")";
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
            $data = mdlResumeOp::instance()->getDeliverLogById($id);
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

    /**
     * 删除简历
     */
    public function funcDelete()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt();
        if(!$id){
            $this->output('101:缺少参数');
        }
        // 1. 删除用户
    }

    /**
     * 导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function funcExport()
    {
        $this->needLogin();

        $fileName = "简历库excel_" . date('Ymd', time());
        $objPHPExcel = new \PHPExcel();
        $limit = min(1000, $this->defaultPerPage);
        $cond = $this->_getFilter();
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlResumeOp::instance()->getDeliverLogList($cond, $page, $limit);
        $data = $data['data'] ?: [];
        if($data){
            $data = array_values($data);
            $jobIds = \clsTools::mkKey($data, 'job_id', true);
            $jobs = mdlJob::instance()->getJobNameById($jobIds);
            $uids = \clsTools::mkKey($data, 'uid', true);
            $users = mdlResumeOp::instance()->getResumeByUids($uids);

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '#')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '职位')
                ->setCellValue('D1', '电话')
                ->setCellValue('E1', '性别')
                ->setCellValue('F1', '学历')
                ->setCellValue('G1', '学校')
                ->setCellValue('H1', '专业')
                ->setCellValue('I1', '毕业时间')
                ->setCellValue('J1', '邮箱')
                ->setCellValue('K1', '渠道')
                ->setCellValue('L1', '求职状态');
            $num = 2;
            foreach ($data as $k => $v) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $k+1)
                    ->setCellValue('B' . $num, $users[$v['uid']]['name'])
                    ->setCellValue('C' . $num, $jobs[$v['job_id']])
                    ->setCellValue('D' . $num, $users[$v['uid']]['phone'])
                    ->setCellValue('E' . $num, $users[$v['uid']]['sex'])
                    ->setCellValue('F' . $num, $users[$v['uid']]['degree'])
                    ->setCellValue('G' . $num, $users[$v['uid']]['school'])
                    ->setCellValue('H' . $num, $users[$v['uid']]['major'])
                    ->setCellValue('I' . $num, $users[$v['uid']]['graduation_date'])
                    ->setCellValue('J' . $num, $users[$v['uid']]['email'])
                    ->setCellValue('K' . $num, $v['from'] == 1 ? '官网' : '微信招聘')
                    ->setCellValue('L' . $num, \defConst::$audit_status[$v['status']]);
                $num++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle($fileName);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}