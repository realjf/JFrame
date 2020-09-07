<?php


require_once "base.php";


class loginCount
{
    public function run()
    {
        $date = date("Ymd", strtotime("yesterday"));
        // 入库昨天的登录数据
        $uvCount = intval(\Model\mdlRedis::instance("online")->get(\defRedisKeys::LOGIN_USER_VIEW . SYS_ENV . $date));
        $pvCount = intval(\Model\mdlRedis::instance("online")->get(\defRedisKeys::LOGIN_PAGE_VIEW . SYS_ENV . $date));
        $res = \Model\mdlLoginCount::instance()->add($date, $uvCount, $pvCount);
        showLog("date：{$date}\tuv：{$uvCount}\tpv：{$pvCount}");
    }
}

$loginCount = new loginCount();
$loginCount->run();

