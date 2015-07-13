<?php

/**
 * 
 * 监控进程启动时，根据监控配置，更新监控服务的
 * @author young<young@yeahmobi.com>
 *
 */
class Action_Update extends Yaf_Action_Abstract
{
	
	public $model = NULL;
	
    public function execute() {

    	$this->init();

    	$params      = $this->getRequest()->getParams();
    	$checkResult = isset($params['checkResult']) ? $params['checkResult'] : '';
    	$monitorConfig = isset($params['monitorConfig']) ? $params['monitorConfig'] : array();
    	$lastStatus = isset($params['lastStatus']) ? $params['lastStatus'] : 3;
    	
    	$this->updateCheckResult($checkResult, $monitorConfig, $lastStatus);
    	
        return FALSE;
    }
    
    public function init() {
    	
    	$this->model = new Model_Alarm_Monitorcore;
    }
    
	public function updateCheckResult($checkResult='', $monitorConfig=array(), $lastStatus=0) {
	
		if ( empty($checkResult) OR empty($monitorConfig)) self::output(FALSE, 'check result is empty or error!');
		
		$status_change_time = 0;
		$next_check_time    = 0;
		$data               = array();
		$this->checkResultArr = $checkResult;
		$status_detail	    = '';
		$logid 				= '';
		$log 				= '';

		
		$id 			      = $monitorConfig['id'] ? $monitorConfig['id'] : self::output(FALSE, 'service id is empty or error!');
		$last_check_time      = $this->checkResultArr['time'] ? $this->checkResultArr['time']: time();
		$checkTime            = $monitorConfig['monitor_check'] ? $monitorConfig['monitor_check'] : 10;
		$next_check_time      = $last_check_time + $checkTime * 60;
		$status 			  = isset($this->checkResultArr['code']) ? $this->checkResultArr['code']: 3;
		
		foreach ($this->checkResultArr['msg'] as $msg) {
						
			$logid .= isset($msg['logid']) ? $msg['logid'].'<br/>' : '';
			
			$log    .= isset($msg['log']) ? str_replace($msg['key'], '<font color="red">'.$msg['key'].'</font>', $msg['log']).'<br/>' : '';
			
			if ($logid) $color = 'red'; else $color = 'green';
			$status_detail .= 'key:'.$msg['key'];
			$status_detail .= ' current_num:<font color='.$color.'>'.$msg['num'].'</font>';
			$status_detail .= ' alarm_num:'.$msg['alarm'];
			$status_detail .= '<br/>';

		}
			
		//$lastStatus = $this->getLastStatus($id);
		if ($lastStatus != $status)  $status_change_time = $last_check_time;
		
		if ($this->checkResultArr) {
			
			
			$data = array(
				'last_check_time' 		=> $last_check_time,
				'next_check_time'		=> $next_check_time,
				'status' 				=> $status,
				'status_detail' 		=> rtrim($status_detail,'<br/>'),
			);
			
			if ($status_change_time) $data['status_change_time'] = $status_change_time;
			if ($logid) {
				$data['logid'] = rtrim($logid,'<br/>');
				$data['log'] = rtrim($log,'<br/>');
			} else {
				$data['logid'] = $data['log'] = '';
			}
			
			$where = "service_id=".$id;
			$this->alarmModel->{__FUNCTION__}('monitor_alarm', $data, $where);
		
		} else {
			
			self::output(FALSE, 'check result error!');
		}
	}
}