<?php
class Model_Monitor_Alarm
{
    private $dao;

    const WARNING  = 1;
    const CRITICAL = 2;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getCount($type = 0) {
        $returns = 0;

        if (empty($type)) {
            $returns = $this->dao->count(Sys_Database::getTable('monitor_alarm'));
        } else {
            $returns = $this->dao->count(Sys_Database::getTable('monitor_alarm'), array("status" => $type));
        }

        return $returns;
    }

} 