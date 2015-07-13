<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Monitorcore
{

	/**
	 * 模型表
	 */
	public $monitorAlarmTable;
    public $monitorAlarmConfigTable;
    public $monitorLogConfigTable;
	public $datawareKeyTable;
	public $extractRecordTable;

	/**
	 * 访问初始化
	 */
	public function __construct()
	{
		$this->dao = new Ym_Dao('default');

        $this->monitorAlarmTable        = Sys_Database::getTable('monitor_alarm');
        $this->monitorAlarmConfigTable  = Sys_Database::getTable('monitor_alarmconfig');
        $this->monitorLogConfigTable    = Sys_Database::getTable('monitor_logconfig');
        $this->datawareKeyTable         = Sys_Database::getTable('dataware_key');
        $this->extractRecordTable       = Sys_Database::getTable('extract_record');
	}
	
	public function getDataFromMonitorConfig() {
	
		$sql = 'select id from '.$this->monitorAlarmConfigTable.' where monitor_status=1';
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getDataFromMonitorAlarm() {
		
		$sql = 'select service_id from ' . $this->monitorAlarmTable;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getCheckResult($key, $log_app_id, $last, $then) {
		
		$sql  = "select sum(num) as num,content_id,sample from ".$this->datawareKeyTable;
		$sql .= " where `time`=".$then;
		$sql .= " and `key`='".$key."' and `log_app_id`=".$log_app_id;
		
		return $this->dao->queryRow($sql, true);
	}
	
	public function getCheckMaxTime($key='weblog') {
	
		$sql = "select time from ".$this->extractRecordTable." where `name`='".$key."' limit 1";
		//Ym_Logger::info($sql);
		return $this->dao->queryRow($sql, true);
	}
	
	public function insertData(array $data = array()) {
		
		$serviceids = '';
    	foreach ($data as $serviceid) {
    		$serviceids .= "(".$serviceid."),";
    	}
		$sql = "insert into ".$this->monitorAlarmTable." (service_id) values ".rtrim($serviceids, ',');
		
		return $this->dao->query($sql);
	}
	
	public function deleteData(array $data = array()) {
		
		$serviceids = '(';
    	foreach ($data as $serviceid) {
    		$serviceids .= $serviceid.",";
    	}
    	$serviceids = rtrim($serviceids, ',').")";
		$sql = "delete from ".$this->monitorAlarmTable." where service_id in ".$serviceids;
		
		return $this->dao->query($sql);
	}
	
	public function updateTime() {
		
		
		$sql = "update ".$this->monitorAlarmTable." set `next_check_time`=".(strtotime(date('Y-m-d H:i',time()))+10).",`last_notify_time`=0";
		return $this->dao->query($sql);
	}
	
	public function getMonitorConfig($id=0) {
		
		$sql = "select a.id,a.monitor_app,a.monitor_service,a.monitor_check,a.monitor_retry,a.monitor_url,a.monitor_param,
				a.notify_status,a.notify_contact,a.notify_userid,a.notify_groupid,
				a.notify_method,a.notify_type,a.notify_period,a.notify_interval,
				l.monitor_ip,l.monitor_app as app_name 
				from ". $this->monitorAlarmConfigTable ." as a,". $this->monitorLogConfigTable ." as l
				where a.monitor_app=l.id and a.id=".$id;
		
		return $this->dao->queryRow($sql, true);
	}
	
	public function getCheckServiceIds($time=NULL) {
		
		if ($time===NULL) $time = time();
		
		$sql = "select a.service_id,a.status,a.last_check_time,a.next_check_time,a.last_notify_time,c.monitor_api  
				from ". $this->monitorAlarmTable ." as a,". $this->monitorAlarmConfigTable ." as c
				where a.next_check_time<".$time." and a.service_id=c.id";
		return $this->dao->fetchAll($sql, '', true);
	}
	
	public function getLastStatus($id) {
		
		$sql = "select status from monitor_alarm where service_id=".$id;
		return $this->dao->queryRow($sql, true);
	}
	
	public function getListFromAlarm($start=0, $limit=10, $level='a') {
		
		$sql_1 = "select c.notify_status,c.monitor_service,c.notify_interval,a.last_notify_time,a.service_id,a.last_check_time,a.next_check_time,a.status_change_time,a.status,a.status_detail";
		$sql_2 = "select count(*) as num ";
		
		if ($level=='a') $status='';
		if ($level=='o') $status='and a.status=0';
		if ($level=='w') $status='and a.status=1';
		if ($level=='c') $status='and a.status=2';
		$sql = " from ". $this->monitorAlarmTable ." as a,". $this->monitorAlarmConfigTable ." as c
				where a.service_id=c.id ".$status." Order by a.status desc";
		
		if ($start==-1) return $this->dao->queryRow($sql_2.$sql, true);
		
		$sql = $sql_1.$sql." limit ".$start.",".$limit;
		return $this->dao->fetchAll($sql, '', true);
	}
	
	public function getLog($id) {
		
		$sql = "select log from ".$this->monitorAlarmTable." where `service_id`=".$id." limit 1";
		return $this->dao->queryRow($sql, true);
	}
	
	
	public function updateCheckResult($table, $data, $where) {

		return $this->dao->update($table, $data, $where);
	}
}