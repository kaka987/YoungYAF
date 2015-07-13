<?php

class Model_Analytics_Times {

    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getList($hosts, $from, $to, $skip, $limit) {
        $returns = array();

        $where   = sprintf("`time` > %d AND `time` <= %d", $from, $to);
        $where   .= empty($hosts) ? '' : " AND `times`.`host_id` in ({$hosts})";
        $limit   = 'LIMIT ' . $skip . ',' . $limit;

        $query   = "SELECT `host`,`path`,max(`max_response_time`) AS `max_response_time`,(`total_response_time` / `num`) AS `avg_response_time` FROM `". Sys_Database::getTable('dataware_times') ."` AS times LEFT JOIN ". Sys_Database::getTable('relation_path') ." AS path ON path.id = times.path_id WHERE {$where} GROUP BY `path_id` ORDER BY `avg_response_time` DESC {$limit}";

        $returns = $this->dao->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    public function getCount($hosts, $from, $to) {
        $returns = 0;

        $where   = sprintf("`time` > %d AND `time` <= %d", $from, $to);
        $where   .= empty($hosts) ? '' : " AND `times`.`host_id` in ({$hosts})";

        $query   = "SELECT count(`count`) AS `count` FROM (SELECT count(*) AS `count` FROM `". Sys_Database::getTable('dataware_times') ."` AS times LEFT JOIN `". Sys_Database::getTable('relation_path') ."` AS path ON path = times.path_id WHERE {$where} GROUP BY `path_id` ) AS `data`";

        $res     = $this->dao->query($query)->fetch(PDO::FETCH_ASSOC);
        $returns = $res['count'];

        return $returns;
    }
} 