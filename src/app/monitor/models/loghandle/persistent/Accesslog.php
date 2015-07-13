<?php
class Model_Loghandle_Persistent_Accesslog
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    /**
     * 获取访问趋势
     * @param $hosts
     * @param $from
     * @param $to
     * @return array
     */
    public function getAccessTrend($hosts, $from, $to, $time) {
        $returns = array();

        $where = "WHERE `time`> {$from} AND `time` <= {$to} ";

        if (! empty($time)) {
            $where = "WHERE `time` = {$time}";
        }

        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $sql = "SELECT `time`,sum(`num`) AS num FROM `". Sys_Database::getTable('dataware_times') ."` {$where} GROUP BY `time`";

        $query = $this->dao->query($sql);
        $returns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    /**
     * 获取错误趋势
     * @param $hosts
     * @param $from
     * @param $to
     * @param $status
     * @return array
     */
    public function getErrorTrend($hosts, $from, $to, $status) {
        
        $returns = array();
        $id_start = $id_end = NULL;

        if (!empty($from)) {
            $sql = "select id from ".Sys_Database::getTable('log_uhps')." where time>=$from limit 1";

            $query = $this->dao->query($sql);
            $id_res = $query->fetchAll(PDO::FETCH_ASSOC);
            $id_start = isset($id_res[0]['id']) ? $id_res[0]['id'] : '';
        }

        if (!$id_start) return array();

        if (!empty($to)) {
            $sql = "select id from ".Sys_Database::getTable('log_uhps')." where time>=$to limit 1";
            $query = $this->dao->query($sql);
            $id_res = $query->fetchAll(PDO::FETCH_ASSOC);
            $id_end = isset($id_res[0]['id']) ? $id_res[0]['id'] : '';
        }

        $where  = $to ? " and u.id> {$id_start} and u.id<={$id_end} " : " and u.id> {$id_start} " ;
        $where .= empty($hosts) ? '' : " AND u.host_id in ({$hosts})";
        $where .= " AND u.code in (". $status .")";

        //$sql = "SELECT `status`.time,`status`.status,`status`.num,path.host,path.path  FROM `".Sys_Database::getTable('log_uhps')."` AS `u` LEFT JOIN `".Sys_Database::getTable('relation_path')."` AS path ON `status`.path_id = path.id {$where} ORDER BY `time` ASC";

        $sql = "select u.time,u.code,u.num,p.host,p.path
                from ".Sys_Database::getTable('log_uhps')." as u,".Sys_Database::getTable('relation_path')." as p
                where u.path_id=p.id ".$where;//." order by time desc";
        $query = $this->dao->query($sql);
        $returns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    /**
     * @param $hosts
     * @param $from
     * @return array
     */
    public function getRipTopTen($hosts, $from) {
        $returns = array();

        $where = "WHERE `time`= {$from} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $sql    = "SELECT `ip`,`num`,`path`,`host` FROM `". Sys_Database::getTable('dataware_ip') ."` AS ip LEFT JOIN ".Sys_Database::getTable('relation_path')." AS path ON ip.path_id = path.id {$where} ORDER BY `num` DESC limit 10";

        $query = $this->dao->query($sql);
        $returns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    /**
     * @param $hosts
     * @param $from
     * @return mixed
     */
    public function getRtcTopTen($hosts, $from) {
        $returns = array();

        $where = "WHERE `time`= {$from} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $sql    = "SELECT `ip`, `total_request_time`,`path`,`host` FROM `". Sys_Database::getTable('dataware_ip') ."` AS ip LEFT JOIN ".Sys_Database::getTable('relation_path')." AS path ON ip.path_id = path.id {$where} ORDER BY `total_request_time` DESC limit 10";

        $query = $this->dao->query($sql);
        $returns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    public function getErrorTopTen($hosts, $from, $to, $status) {
        $returns = array();

        $where = "WHERE status.`time`> {$from} AND status.`time` <= {$to} ";
        $where .= empty($hosts) ? '' : " AND status.`host_id` in ({$hosts})";
        $where .= " AND status.status in (". $status .")";

        $sql    = "SELECT `time`, `host`, `path`,`status`,sum(num) AS num FROM `".Sys_Database::getTable('dataware_status')."` AS `status` LEFT JOIN `".Sys_Database::getTable('relation_path')."` AS path ON status.`path_id` = path.`id` {$where} GROUP BY path.`path`,`status` ORDER BY `num` DESC LIMIT 10";

        $query = $this->dao->query($sql);

        $returns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

//    public function mapStatistics($where)
//    {
////		$dao    = new Ym_Dao('default');
//        $sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num`,sum(`total_request_time`) as `total_request_time`,max(`max_request_time`) as `max_request_time` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code`";
//        $result = self::getIns()->fetchAll($sql, false, true);
//        return $result;
//    }
//
//    public function mapRequestTime($where)
//    {
////		$dao    = new Ym_Dao('default');
//        $sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num`,sum(`total_request_time`) as `total_request_time`,max(`max_request_time`) as `max_request_time` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code`";
//        $result = self::getIns()->fetchAll($sql, false, true);
//        return $result;
//    }
//
//    public function mapStatisticsTopTen($where)
//    {
////		$dao    = new Ym_Dao('default');
//        $sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code` ORDER BY `num` DESC LIMIT 10";
////        echo $sql;exit;
//        $result = self::getIns()->fetchAll($sql, false, true);
//        return $result;
//    }
} 
