<?php
class Model_User_Setting {

    public $userBusinessTable;
    public $relationBusinessTable;
    public $dao;

    public function __construct() {
        $this->dao = new Ym_Dao('log');

        $this->userBusinessTable     = Sys_Database::getTable('user_business');
        $this->relationBusinessTable = Sys_Database::getTable('relation_business');
        $this->relationHostTable = Sys_Database::getTable('relation_host');
    }

    public function get($uid) {
        $uid = addslashes($uid);

        $sql = sprintf("SELECT * FROM {$this->userBusinessTable} WHERE `uid` = %d", $uid);
        $result = $this->dao->fetchAll($sql, false, true);

        return $result;
    }

    public function getBusinessSetting($urlIds=NULL) {

        if ($urlIds == NULL) return FALSE;
        $sql = "select * from {$this->relationHostTable} where id in (".$urlIds.")";
        return $this->dao->fetchAll($sql, false, true);
    }

    public function openBusiness($uid=1, $business=NULL) {
        $uid = addslashes($uid);
        $business = addslashes($business);

        $where = sprintf("`bid` in (%s)",  $business);
        $sql = "SELECT * FROM {$this->userBusinessTable} WHERE {$where}";
        $businessResult = $this->dao->fetchAll($sql, false, true);

        $business = explode(",", $business);

        foreach ($businessResult AS $result) {
            foreach ($business AS $index => $value) {
                if ($value == $result['bid']) {
                    unset($business[$index]);
                }
            }
        }

        $businessIds = implode(",", $business);
        if ($businessIds) {
            $where = "id in ({$businessIds})";
            $sql = "SELECT * FROM {$this->relationBusinessTable} WHERE {$where}";
            $business = $this->dao->fetchAll($sql, false, true);

            $values = "";
            foreach ($business AS $value) {
                $values .= "({$uid},{$value['id']},'{$value['name']}'),";
            }
        }


        // record already exists
        if (empty($values)) {
            $result = false;
        } else {
            $values = substr($values, 0, strlen($values) - 1);
            $sql = "INSERT INTO {$this->userBusinessTable}(`uid`,`bid`,`bname`) VALUES {$values}";
            $result = $this->dao->query($sql);
        }
        return $result;
    }

    public function closeBusiness($uid=1, $business=NULL) {

        $business = addslashes($business);
        $where = sprintf("`uid` = %d AND `bid` in (%s)", $uid, $business);
        $result = $this->dao->delete($this->userBusinessTable, $where);

        return $result;
    }
}