<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Monitorupdate
{

	/**
	 * 访问初始化
	 */
	public function __construct()
	{
		$this->dao = new Ym_Dao('default');
	}
	
	public function updateCheckResult($checkResult, $monitorData, $lastStatus, $enableNotify) {

		if ( empty($checkResult) OR empty($monitorData)) Ym_CommonTool::myoutput(FALSE, 'check result is empty or error!');
		
		$status_change_time = 0;
		$next_check_time    = 0;
		$data               = array();
		$status_detail	    = '';
		$logid 				= '';
		$log 				= '';

		$id 			      = $monitorData['id'] ? $monitorData['id'] : Ym_CommonTool::myoutput(FALSE, 'service id is empty or error!');
		$last_check_time      = $checkResult['time'] ? $checkResult['time']: time();
		$checkTime            = $monitorData['monitor_check'] ? $monitorData['monitor_check'] : 1;
		$next_check_time      = $last_check_time + $checkTime * 60;
		
		$status 			  = isset($checkResult['code']) ? $checkResult['code']: 3;
		
		if ($lastStatus != $status)  $status_change_time = $last_check_time;
		
		if ($checkResult) {
			
			$data = array(
				'last_check_time' 		=> $last_check_time,
				'next_check_time'		=> $next_check_time,
				'status' 				=> $status,
				'status_detail' 		=> $checkResult['msg'],
			);
			
			if ($status_change_time) $data['status_change_time'] = $status_change_time;
			
			if ($enableNotify) $data['last_notify_time'] = strtotime(date('Y-m-d H:i',time()));
			//Ym_Logger::info('enableNotify:'.$enableNotify.'#'.$data['last_notify_time']);
			if ($checkResult['logid']) {
				$data['logid'] = rtrim($checkResult['logid'],'<br/>');
				$data['log'] = rtrim($checkResult['log'],'<br/>');
			} else {
				$data['logid'] = $data['log'] = '';
			}
			
			$where = "service_id=".$id;
			$this->updatedata('monitor_alarm', $data, $where);
		
		} else {
			
			Ym_CommonTool::myoutput(FALSE, 'check result error!');
		}
	}
	
	public function updatedata($table, $data, $where) {

		return $this->dao->update($table, $data, $where);
	}
}