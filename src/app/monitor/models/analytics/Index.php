<?php
class Model_Analytics_Index
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getPageVisit($hosts, $from, $to){
        $returns = 0;

        $where = sprintf("WHERE `time` > %d AND `time` <= %d %s", $from, $to, empty($hosts) ? "" : " AND `host_id` in ({$hosts})");
        $sql = sprintf("SELECT SUM(num) AS num FROM `%s` %s", Sys_Database::getTable('dataware_times'), $where);

        $result = $this->dao->query($sql)->fetch(PDO::FETCH_ASSOC);
        $returns = $result['num'];

        return $returns;
    }

    public function getUserVisit($hosts, $from, $to){
        $returns = 0;

        $where = sprintf("WHERE `time` > %d AND `time` <= %d %s", $from, $to, empty($hosts) ? "" : " AND `host_id` in ({$hosts})");

        $sql = sprintf("SELECT count(distinct `ip`) AS `num` FROM `%s` %s", Sys_Database::getTable('dataware_ip'), $where);

        $result = $this->dao->query($sql)->fetch(PDO::FETCH_ASSOC);
        $returns = $result['num'];

        return $returns;
    }

    public function getErrorNumber($hosts, $from, $to, $status = null){
        $returns = 0;

        $whereStatus = empty($status) ? "AND `status` >= 500" : "AND `status` IN ({$status})";
        $whereHost   = empty($hosts) ? "" : " AND `host_id` in ({$hosts})";

        $where = sprintf("WHERE `time` > %d AND `time` <= %d %s %s", $from, $to, $whereStatus, $whereHost);

        $sql = sprintf("SELECT sum(`num`) AS num FROM `%s` %s", Sys_Database::getTable('dataware_status'), $where);

        $result = $this->dao->query($sql)->fetch(PDO::FETCH_ASSOC);
        $returns = $result['num'];

        return $returns;
    }
} 