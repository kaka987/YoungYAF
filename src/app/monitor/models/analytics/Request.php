<?php
class Model_Analytics_Request
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getMinute($flag, $hosts, $from, $to) {
        $returns = array();

        $column = '`time`,sum(`num`) AS `num`';
        $where  = sprintf("WHERE `time` > %d AND `time` <= %d", $from, $to);
        $where  .= empty($hosts) ? '' : " AND `". Sys_Database::getTable('dataware_times') ."`.`host_id` in (". $hosts .")";
        $group  = 'GROUP BY `time`';
        $order  = '';

        switch ($flag) {
            case 'max':
                $order = 'ORDER BY `num` DESC';
                break;
            case 'min':
                $order = 'ORDER BY `num` ASC';
                break;
        }

        $query = sprintf("SELECT %s FROM `%s` %s %s %s ", $column, Sys_Database::getTable('dataware_times'), $where, $group, $order);

        $returns = $this->dao->query($query)->fetch(PDO::FETCH_ASSOC);

        return $returns;
    }

} 