<?php

class defConst
{
    /**
     * 应聘渠道
     * @var array
     */
    public static $_promotes = [

    ];

    public static $_families = [

    ];

    public static $_eduType = [

    ];

    /**
     * 招聘岗位类别
     * @var array
     */
    public  static $_category = [

    ];

    /**
     * 职位分类描述
     * @var array
     */
    public static $_cDes = [

    ];

    public static $_degree = [

    ];

    public static $_sex = [

    ];

    public static $_marriage = [

    ];

    /**
     * 简历投递备注信息模板
     * @var array
     */
    public static $_remarks = [

    ];

    /**
     * 回复用户投递进度信息
     * @var array
     */
    public static $_remarksForUser = [

    ];

    /**
     * 简历申请进度
     * @var array
     */
    public static $apply_status = [

    ];

    /**
     * 简历审核进度
     * @var array
     */
    public static $audit_status = [

    ];

    /**
     * 简历审核 未通过状态
     * @var array
     */
    public static $auditRejectStatus = [

    ];

    /**
     * 简历进程显示状态
     * @var array
     */
    public static $auditProcessStatus = [

    ];

    const CODE_UPLOAD_CANT_WRITE = 7;
    const CODE_UPLOAD_NO_TMP_DIR = 6;
    const CODE_FILE_NOT_UPLOAD = 4;
    const CODE_FILE_PARTIAL_UPLOAD = 3;
    const CODE_OVER_MAX_FILE_SIZE = 2;
    const CODE_OVER_UPLOAD_MAX_FILE_SIZE = 1;
    const CODE_FILE_TYPE_NOT_ALLOW = -1;
    const CODE_SIZE_TOO_LARGE = -2;
    const CODE_UPLOAD_FAILED = -3;
    const CODE_BUILD_UPLOAD_DIR_FAILED = -4;
    const CODE_UPLOAD_FILE_PATH_EMPTY = -5;
}