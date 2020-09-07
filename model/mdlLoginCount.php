<?php


namespace Model;


class mdlLoginCount extends mdlBase
{
    const TBL_LOGIN_COUNT = "login_count"; // 登录统计

    public function add($date, $uv, $pv)
    {
        $date = intval($date);
        if(!$date){
            return false;
        }
        $cond = [
            "date" => $date
        ];
        $set = [
            "pv" => intval($pv),
            "uv" => intval($uv),
            "ctime" => time()
        ];
        $old = $this->dbOnline()->select(self::TBL_LOGIN_COUNT, "*", $cond);
        if($old){
            return $this->dbOnline()->update(self::TBL_LOGIN_COUNT, $set, $cond);
        }else{
            $set["date"] = $date;
            return $this->dbOnline()->insert(self::TBL_LOGIN_COUNT, $set);
        }
    }
}