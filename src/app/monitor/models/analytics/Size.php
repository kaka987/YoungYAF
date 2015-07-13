<?php
class Model_Analytics_Size
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getList($hosts, $from, $to, $skip, $limit) {
        $returns = array();

        $having  = empty($hosts) ? '' : "HAVING `host_id` in ({$hosts})";

        $where = sprintf("`time` = %d ", $from);

        if (! empty($to)) {
            $day = ($to - $from) / 86400;
            for($i = 1; $i <= $day; $i++) {
                $where .= "OR `time` = " . ($from + ($i * 86400));
            }
        }

        $limit   = 'LIMIT ' . $skip . ',' . $limit;

        $query   = "SELECT `host`, `path`, sum(`size`) AS `size`,`path`.`host_id` FROM `". Sys_Database::getTable('dataware_size') ."` AS size LEFT JOIN ". Sys_Database::getTable('relation_path') ." AS path ON path.id = size.path_id WHERE {$where} GROUP BY `path_id` {$having} ORDER BY `size` DESC {$limit}";

        $returns = $this->dao->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $returns;
    }

    public function getCount($hosts, $from, $to) {
        $returns = 0;

        $where = sprintf("`time` = %d ", $from);

        if (! empty($to)) {
            $day = ($to - $from) / 86400;
            for($i = 1; $i <= $day; $i++) {
                $where .= " OR `time` = " . ($from + ($i * 86400));
            }
        }

        $where   .= empty($hosts) ? '' : " AND `size`.`host_id` in ({$hosts})";

        $query   = "SELECT count(`count`) AS `count` FROM (SELECT count(*) as `count` FROM `". Sys_Database::getTable('dataware_size') ."` AS size WHERE {$where} GROUP BY `path_id`) AS `data`";

        $res     = $this->dao->query($query)->fetch(PDO::FETCH_ASSOC);
        $returns = $res['count'];

        return $returns;
    }
} 