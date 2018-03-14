<?php

class defConst
{
    /**
     * 应聘渠道
     * @var array
     */
    public static $_promotes = [
        1 => '4399微信/官网',
        2 => '厦门人才网',
        3 => '智联招聘',
        4 => '前程无忧',
        5 => '拉勾网',
        6 => '校园招聘',
        7 => '现场招聘会',
        8 => '58同城',
        9 => '内部推荐',
        10 => '猎头推荐',
        11 => '其他',
    ];

    public static $_families = [
        1   => '父亲',
        2   => '母亲',
        3   => '兄弟',
        4   => '姐妹',
        5   => '其他',
    ];

    public static $_eduType = [
        0 => '请选择',
        1 => '全日制',
        2 => "自考",
        3 => '成人',
        4 => '其他',
    ];

    /**
     * 招聘岗位类别
     * @var array
     */
    public  static $_category = [
        1 => '校园招聘',
        2 => '技术研发类',
        3 => '产品策划类',
        4 => '运营编辑类',
        5 => '美术设计类',
        6 => '职能管理类',
        0 => '所有岗位',
    ];

    /**
     * 职位分类描述
     * @var array
     */
    public static $_cDes = [
        1 => '校园招聘及实习生招聘 ...',
        2 => 'web开发、移动开发、游戏开发、软件测试、数据研发、运维安全 ...',
        3 => '产品经理、游戏策划、游戏测试 ...',
        4 => '游戏运营、审核、编辑、市场商务、游戏客服 ...',
        5 => '视觉设计、交互设计、游戏美术、游戏特效 ...',
        6 => '人力、行政、法务、财务、公关 ...',
    ];

    public static $_degree = [
        0 => '请选择',
        1 => '大专以下',
        2 => '大专',
        3 => '本科',
        4 => '硕士',
        5 => '硕士以上'
    ];

    public static $_sex = [
        1 => "男",
        2 => "女",
        0 => "未设置"
    ];

    public static $_marriage = [
        1 => '未婚',
        2 => '已婚',
        0 => '其他',
    ];

    /**
     * 简历投递备注信息模板
     * @var array
     */
    public static $_remarks = [
        0 => "您的简历已投递成功！<br/>如通过初筛，我们将在2个工作日内联系您！",
        1 => "您的简历已投递成功！<br/>如通过初筛，我们将在2个工作日内联系您！",
        2 => "您目前处于面试环节！<br/>面试地址：软件园二期望海路2号A区一楼<br/>具体情况请留意短信和邮箱",
        3 => "您目前处于体检环节！<br/>体检地址：厦门大学附属中山医院厦禾健康体检部，具体情况请留意邮箱",
        4 => "恭喜，您已被录用！<br/>具体情况我们将电话联系您",
        5 => "很抱歉，您的简历与公司目前招聘要求不太匹配<br/>感谢您的关注",
    ];

    /**
     * 回复用户投递进度信息
     * @var array
     */
    public static $_remarksForUser = [
        0 => '<div class="m-sta_in">您的简历已<span class="txt">投递成功！</span></div><div class="app2-status-if"><p>如通过初筛，我们将在2个工作日内联系您！</p></div>',
        1 => '<div class="m-sta_in">您的简历已<span class="txt">投递成功！</span></div><div class="app2-status-if"><p>如通过初筛，我们将在2个工作日内联系您！</p></div>',
        2 => '<div class="m-sta_in">您目前处于<span class="txt">面试</span>环节！</div><div class="app2-status-if"><p>面试地址：软件园二期望海路2号A区一楼</p><p>具体情况请留意短信和邮箱</p></div>',
        3 => '<div class="m-sta_in">您目前处于<span class="txt">体检</span>环节！</div><div class="app2-status-if"><p>体检地址：厦门大学附属中山医院厦禾健康体检部</p><p>具体情况请留意邮箱</p></div>',
        4 => '<div class="m-sta_in">恭喜，您已被<span class="txt">录用！</span></div><div class="app2-status-if">具体情况我们将电话联系您</div>',
        5 => '<div class="app2-status-if"><p>很抱歉，您的简历与公司目前招聘要求不太匹配</p><p>感谢您的关注</p></div>',
    ];

    /**
     * 简历申请进度
     * @var array
     */
    public static $apply_status = [
        0 => "投递成功",
        1 => "对我有意",
        2 => "邀请面试",
        3 => "体检",
        4 => "录用",
        5 => "未通过",
    ];

    /**
     * 简历审核进度
     * @var array
     */
    public static $audit_status = [
        -8 => '放弃录用',
        -7 => '放弃面试',
        -6 => '放弃测试',
        -5 => '简历储备',
        -4 => '体检未通过',
        -3 => '面试未通过',
        -2 => '测试未通过',
        -1 => '简历未通过',
        0 => "新简历", // 兼容旧版遗留状态
        1 => "新简历",
        2 => "面试",
        3 => "offer",
        4 => "入职",
        5 => "未通过",
    ];

    /**
     * 简历审核 未通过状态
     * @var array
     */
    public static $auditRejectStatus = [
        -8 => '放弃录用',
        -7 => '放弃面试',
        -6 => '放弃测试',
        -5 => '简历储备',
        -4 => '体检未通过',
        -3 => '面试未通过',
        -2 => '测试未通过',
        -1 => '简历未通过',
    ];

    /**
     * 审核进程状态
     * @var array
     */
    public static $auditProcessStatus = [
        1 => "新简历",
        2 => "面试",
        3 => "offer",
        4 => "入职",
        5 => "未通过",
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