<?php
/**
 * Created by PhpStorm.
 * User: 4399-3046
 * Date: 2018/2/23
 * Time: 16:42
 */

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
}