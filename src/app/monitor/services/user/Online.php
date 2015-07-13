<?php
class Service_User_Online
{
    public $modelUserOnline;

    public function __construct() {
        $this->modelUserOnline = new Model_User_Online();
    }

    public function refresh($uid) {
        $returns = null;

        $activeTime = time();

        $userOnline = $this->modelUserOnline->getUser($uid);

        if($userOnline) {
            $diffTime  = $activeTime - $userOnline['active_time'];
            $totalTime = $userOnline['total_time'] + $diffTime;

            $userOnline = array(
                'uid'        => $uid,
                'activeTime' => $activeTime,
                'totalTime'  => $totalTime
            );

            $returns = $this->modelUserOnline->refreshUser($userOnline);
        }

        $this->modelUserOnline->removeOfflineUser();

        return $returns;
    }

    public function getTotal($userOnline = array()) {
        $returns = $this->modelUserOnline->getUserCount($userOnline);

        return $returns;
    }

    public function getList($userOnline = array(), $limit, $page) {
        $returns = null;

        $total = $this->getTotal($userOnline);

        $count = ceil($total / $limit);
        $skip  = ($page -1) * $limit;
        $data  = $this->modelUserOnline->getUserList($userOnline, $skip, $limit);

        $returns = array("count" => $count, "data" => $data);

        return $returns;
    }
}