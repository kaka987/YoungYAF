<?php


class Model_System_Region
{
    /**
     * region table
     * @var string
     */
    public $dao;

    public function __construct() {
        $this->dao = new Ym_Dao('log');
    }

    public function getList() {
        $sql = "SELECT * FROM `". Sys_Database::getTable('relation_region') ."`";

        $ret = $this->dao->fetchAll($sql, false, true);

        return $ret;
    }

    public function getOne($region) {
        $returns = null;

        $domain = isset($region['domain']) ? trim($region['domain']) : null;

        $where = '';

        if (empty($domain)) {
            return $returns;
        } else {
            $where .= "`domain` = '{$domain}'";
        }

        $where = empty($where) ? $where : "WHERE " . $where;

        $sql = "SELECT * FROM `". Sys_Database::getTable('relation_region') ."` {$where}";

        $returns = $this->dao->queryRow($sql, false, true);

        return $returns;

    }

}
