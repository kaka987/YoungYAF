<?php
class Model_Analytics_Access
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo('log');
    }

    public function getList($hostPathID, $path, $from, $to, $skip, $limit, $condition = array()) {

        $fromID = $this->getIDByTime($from);
        $toID = $this->getIDByTime($to);
        $returns = array();
        $whereTMP = '';
        $where   = sprintf("`status`.`id` > %d AND `status`.`id` <= %d", $fromID[0]['id'], $toID[0]['id']);

        if ( ! empty($hostPathID)) {
            $where  .= " AND (";
            foreach($hostPathID as $hp) {

                if(empty($path)) 
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0].") OR ";
                else 
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0]." AND `status`.`path_id` = ".$hp[1].") OR ";
            }
            $where  .= rtrim($whereTMP,'OR ').')';
        }
        $where  .= empty($condition['status']) ? '' : " AND `code` IN (" . $condition['status'] . ")";
        $limit   = 'LIMIT ' . $skip . ',' . $limit;

        $query   = "SELECT `host`,`path`,`code` as `status`,sum(`num`) AS num
        FROM `". Sys_Database::getTable('log_uhps') ."` AS `status`
        LEFT JOIN `". Sys_Database::getTable('relation_path') ."` AS path ON `status`.path_id = path.id
        WHERE {$where}  GROUP BY `path_id`,`code` ";
        
        if (empty($hostPathID)) $query .= " ORDER BY num DESC {$limit}";

        //echo $query;exit;
        $returns = $this->dao->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    public function getCount($hostPathID, $path, $from, $to, $condition = array()) {

        return 100;
        /*
        $returns = 0;
        $whereTMP = '';

        $where   = sprintf("`time` > %d AND `time` <= %d", $from, $to);
        
        if ( ! empty($hostPathID)) {
            $where  .= " AND (";                                                                                                                                      
            foreach($hostPathID as $hp) {                                                
                if(empty($path))
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0].") OR ";
                else 
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0]." AND `status`.`path_id` = ".$hp[1].") OR ";
            }
            $where  .= rtrim($whereTMP,'OR ').')';
        }
        $where   .= empty($condition['status']) ? '' : " AND `code` IN (" . $condition['status'] . ")";

        $query   = "SELECT count(`count`) AS `count` FROM
        (SELECT count(*) AS `count` FROM `". Sys_Database::getTable('log_uhps') ."` AS status
        WHERE {$where} GROUP BY `path_id`,`code`) AS `data`";

        $res     = $this->dao->query($query)->fetch(PDO::FETCH_ASSOC);
        $returns = $res['count'];

        return $returns;
         */
    }
    
    public function getExistError($hostPathID , $path, $from, $to, $condition) {

        $fromID = $this->getIDByTime($from);
        $toID = $this->getIDByTime($to);
        $returns = array();
        $whereTMP = '';
        $where   = sprintf("`id` > %d AND `id` <= %d", $fromID[0]['id'], $toID[0]['id']);

        if ( ! empty($hostPathID)) {
            $where  .= " AND (";                                                                                                                                      
            foreach($hostPathID as $hp) {                                                
                if(empty($path))
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0].") OR ";
                else 
                    $whereTMP  .= "(`status`.`host_id` = ".$hp[0]." AND `status`.`path_id` = ".$hp[1].") OR ";
            }
            $where  .= rtrim($whereTMP,'OR ').')';
        }
        $where  .= empty($condition['status']) ? '' : " AND `code` IN (" . $condition['status'] . ")";
        $limit   = 'LIMIT 1';

        $query   = "SELECT 1 
        FROM `". Sys_Database::getTable('log_uhps') ."` AS `status`
        WHERE {$where} {$limit}";

        //echo $query;exit;
        $returns = $this->dao->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    public function getIDByTime($time) {
        $sql = "select id from ".Sys_Database::getTable('log_uhps')." where time<={$time} order by id desc limit 1";
        return $this->dao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
} 
