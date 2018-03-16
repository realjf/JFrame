<?php


namespace Module\Admin;


class ctlFile extends ctlBase
{
    public function funcIndex()
    {
        $this->needLogin();
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlResumeFile::instance()->getList([], $page, $this->defaultPerPage);
        $jobNames = $jobFairs = [];
        if($data['data']){
            // 获取职位名称和招聘会
            $jobIds = \clsTools::mkKey($data['data'], 'job_id', true);
            $jobFairIds = \clsTools::mkKey($data['data'], 'job_fair_id', true);
            $jobNames = mdlJob::instance()->getJobNameById($jobIds);
            $jobFairs = mdlJobFair::instance()->getByIds($jobFairIds);
        }
        $this->setPaginator($data['count'], $this->defaultPerPage, $page);
        $this->assign([
            'data' => $data['data'],
            'jobNames' => $jobNames,
            'jobFairs' => $jobFairs,
        ]);
        $this->display('file_index.html');
    }

    /**
     * 下载文件
     */
    public function funcDownload()
    {
        $this->needLogin();
        $id = \clsVars::get('id')->toInt(0);
        if(!$id){
            exit();
        }
        $data = mdlResumeFile::instance()->get($id);
        if(!$data){
            exit();
        }
        $jobInfo = mdlJob::instance()->getJobDetailById($data['job_id']);
        $filePath = mdlFileDownload::$_uploadPath . substr($data['resume_file'], 0, 8) . '/' . $data['resume_file'];
        $fileExt = explode('.', $filePath);
        if($data['title']){
            $title = $data['title'];
        }elseif($data['name'] && $jobInfo['name']){
            $title = $data['name'] . '_' . $jobInfo['name'] . $fileExt[1];
        }else{
            $title = '简历附件' . $fileExt[1];
        }
        $title = html_entity_decode($title);
        if(file_exists($filePath) && is_file($filePath)){
            mdlFileDownload::download($title, $filePath);
        }
        exit();
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

        $fileName = "简历excel_" . date('Ymd', time());
        $objPHPExcel = new \PHPExcel();
        $limit = min(1000, $this->defaultPerPage);
        $page = \clsVars::get('p')->toInt(1, 1);
        $data = mdlResumeFile::instance()->getList([], $page, $limit);
        $data = $data['data'] ?: [];
        if($data){
            $data = array_values($data);
            $jobIds = $jobFairIds = [];
            foreach($data as $v){
                $jobIds[] = $v['job_id'];
                $jobFairIds[] = $v['job_fair_id'];
            }
            $jobs = mdlJob::instance()->getJobNameById($jobIds);
            $jobFairs = mdlJobFair::instance()->getByIds($jobFairIds);


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '#')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '手机号')
                ->setCellValue('D1', '学校')
                ->setCellValue('E1', '邮箱')
                ->setCellValue('F1', '招聘会')
                ->setCellValue('G1', '职位')
                ->setCellValue('H1', '简历文件名')
                ->setCellValue('I1', '申请时间');
            $num = 2;
            foreach ($data as $k => $v) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $k+1)
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['telephone'])
                    ->setCellValue('D' . $num, $v['school'])
                    ->setCellValue('E' . $num, $v['email'])
                    ->setCellValue('F' . $num, $jobFairs[$v['job_fair_id']]['title'] ?: '')
                    ->setCellValue('G' . $num, $jobs[$v['job_id']])
                    ->setCellValue('H' . $num, $v['resume_file'])
                    ->setCellValue('I' . $num, $v['dateline']);
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