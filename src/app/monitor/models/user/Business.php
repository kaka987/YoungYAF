<?php
class Model_User_Business {

    public $relationBusinessTable;
    public $dao;

    public function __construct() {
        $this->dao = new Ym_Dao('log');

        $this->relationBusinessTable = Sys_Database::getTable('relation_business');
    }

    /**
     * Get business count
     * @return int
     */
    public function getCount() {
        $sql = "SELECT count(*) as num FROM " . Sys_Database::getTable('relation_business');
        $result = $this->dao->queryRow($sql, true);

        return $result['num'];
    }

    /**
     * Get business list
     * @param $where
     * @param $limit
     * @return array
     */
    public function get($where, $limit) {
        $sql = "SELECT * FROM ". Sys_Database::getTable('relation_host') ." {$where} {$limit}";
        $result = $this->dao->fetchAll($sql, false, true);

        return $result;
    }

    public function getList($uid) {
        $returns = null;

        $sql = sprintf("SELECT GROUP_CONCAT(`name`) as `business` FROM `%s` WHERE `id` IN(SELECT `bid` FROM `%s` WHERE `uid` = %d)", Sys_Database::getTable('relation_business'), Sys_Database::getTable('user_business'), $uid);
        $result = $this->dao->queryRow($sql, true);

        $returns = $result['business'];

        return $returns;
    }

}
