<?php
class Model_Alarm_GetConfig
{

    public function __construct() {

        $this->dao = new Ym_Dao('log');
    }

    public function getLogConfig($host) {

        $sql = "select * from monitor_logconfig where hostname='".$host."'";
        return $this->dao->fetchAll($sql,'', true);
    }

}