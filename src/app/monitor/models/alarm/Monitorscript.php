<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Monitorscript
{
    /**
     * 模型表
     */
    public $monitorAlarmTable;
    public $datawareKeyTable;
    public $extractRecordTable;
    public $datawareTimesTable;
    public $datawareStatusTable;
    public $relationPathTable;

    /**
     * 访问初始化
     */
    public function __construct() {
        $this->dao = new Ym_Dao('default');

        $this->monitorAlarmTable   = Sys_Database::getTable('monitor_alarm');
        $this->datawareKeyTable    = Sys_Database::getTable('dataware_key');
        $this->extractRecordTable  = Sys_Database::getTable('extract_record');
        $this->datawareTimesTable  = Sys_Database::getTable('dataware_times');
        $this->datawareStatusTable = Sys_Database::getTable('dataware_status');
        $this->relationPathTable   = Sys_Database::getTable('relation_path');
        $this->relationHostTable   = Sys_Database::getTable('relation_host');
        $this->relationServerTable   = Sys_Database::getTable('relation_server');
    }

    public function getCheckResult($key, $log_app_id, $last, $then) {

        $sql  = "select num,content_id,sample from ".$this->datawareKeyTable;
        $sql .= " where `time`=".$then;
        $sql .= " and `key`='".$key."' and `log_app_id`=".$log_app_id;
        //Ym_Logger::info(FALSE,'----------------------'.$sql);
        return $this->dao->queryRow($sql, true);
    }

    public function checkLbsAccess($time=0) {

        $sql = "SELECT sum(`num`) AS num FROM ".$this->datawareTimesTable." WHERE `time`=".$time;
        //Ym_Logger::info(FALSE,'ddd----------------------'.$sql);
        return $this->dao->queryRow($sql, true);
    }

    public function checkLbsError($time=0) {

        $sql = "SELECT sum(`num`) AS num FROM ".$this->datawareStatusTable." WHERE status>=500 AND `time`=".$time;
        return $this->dao->queryRow($sql, true);
    }

    public function checkLbsErrorDetail($time=0) {

        $sql = "select sum(`num`) as num,h.host,h.path,s.status,o.server  
        		from ".$this->datawareStatusTable." as s,".$this->relationPathTable." as h,".$this->relationServerTable." as o
				where s.path_id=h.id and s.server_id=o.id 
				and status>=500 
				and `time` = ".$time." 
				group by path_id,status,server_id order by num desc limit 3";
		//Ym_Logger::info(FALSE,'ddd----------------------'.$sql);
        return $this->dao->fetchAll($sql,'', true);
    }
}