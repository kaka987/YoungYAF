<?php

/**
 * The main process of monitoring
 * 
 */
class Action_Check_current_access extends Yaf_Action_Abstract
{
	
	public $alarmModel = NULL;
	public $serviceId = 0;
	public $lastStatus = 0;
	public $lastTime = 0;
	public $currentTime = 0;
	public $lastNotifyTime = 0;
	public $enableNotify = 0;
	
    public function execute() {

    	$this->init();
    	$this->doMonitor();
        return FALSE;
    }
    
    public function init() {
    	
    	$this->alarmModel = new Model_Alarm_Monitorcore;
    	$this->parseParams();
    }
    
    public function parseParams() {
    	
    	$params      		= $this->getRequest()->getParams();
        $this->serviceId    = isset($params['id']) ? $params['id'] : 0;
        $this->lastStatus  	= isset($params['lastStatus']) ? $params['lastStatus'] : 0;
        $this->lastTime	 	= isset($params['lastTime']) ? $params['lastTime'] : 0;
        $this->currentTime 	= isset($params['currentTime']) ? $params['currentTime'] : 0;
        $this->lastNotifyTime = isset($params['lastNotifyTime']) ? $params['lastNotifyTime'] : 0;
        
        if ($this->serviceId==0 OR $this->lastTime == 0 OR $this->currentTime == 0) {
        	
        	Ym_CommonTool::myoutput(FALSE,'doMonitor params is error!');
        	return FALSE;
        }
        
        $this->plugins(array());
    }

    public function doMonitor() {

        $monitorConfig = $this->getMonitorConfig($this->serviceId); //data from monitor_config
        
		$checkResult = $this->getCheckResult($monitorConfig, $this->lastTime, $this->currentTime);
		
        $enableNotify = $this->ifNotify($monitorConfig);
        
		$updateR = $this->updateCheckResult($checkResult, $monitorConfig, $this->lastStatus, $enableNotify);

		if (isset($monitorConfig['notify_status']) AND $monitorConfig['notify_status']) {
			
			if ($enableNotify)
				$r = $this->doNotify($checkResult, $monitorConfig, $this->lastStatus);
			else 	
				Ym_CommonTool::myoutput(FALSE, 'EnableNotify: '.$enableNotify.' Notify time: '.date('Y/m/d H:i:s',$notifyTime));
		}
		
		return FALSE;//no view
	}
	
	public function getMonitorConfig($id) {
		
		return $this->alarmModel->{__FUNCTION__}($id);
	}

	public function updateCheckResult($checkResult='', $monitorData=array(), $lastStatus=0, $enableNotify=1) {
	
		$updateModel = new Model_Alarm_Monitorupdate;
		return $updateModel->{__FUNCTION__}($checkResult, $monitorData, $lastStatus, $enableNotify);
	}
	
	public function ifNotify($monitorConfig=array()) {
		
		$enableNotify = 0;
        $notifyTime = $monitorConfig['notify_interval'] * 60 + $this->lastNotifyTime;
        if ( (time() >= $notifyTime ) OR $this->lastNotifyTime == 0 OR $monitorConfig['notify_interval']==0) {
        	$enableNotify = 1;
        }
        return $this->enableNotify = $enableNotify;
	}
	
	public function doNotify($checkResult, $monitorConfig, $lastStatus) {
		
		$model = new Model_Alarm_Monitornotify;
		return $model->{__FUNCTION__}($checkResult, $monitorConfig, $lastStatus);
	}
	
	/**
	 * 
	 * 检测脚本钩子程序，每次执行该检测脚本均会执行
	 */
	public function plugins($var=array()) {}
	
	/**
	 * 
	 * 检测脚本主要程序（只需要编写该方法即可）
	 */
	public function getCheckResult($monitorConfig,$lastTime,$currentTime) {
		
		if (empty($monitorConfig)) Ym_CommonTool::myoutput(FALSE, 'get monitor data error or empty!');
		
		$model = new Model_Alarm_Monitorscript;
		
		$id    = isset($monitorConfig['monitor_app']) ? $monitorConfig['monitor_app'] : '';
		$ip    = isset($monitorConfig['monitor_ip']) ? $monitorConfig['monitor_ip'] : '';
		$app   = isset($monitorConfig['app_name']) ? $monitorConfig['app_name'] : 'system';	
		$param = isset($monitorConfig['monitor_param']) ? $monitorConfig['monitor_param'] : '';
		$checkTime = strtotime(date('Y-m-d H:i',$currentTime));
		
    	$code = 0;
    	$maxTime = 0;
    	$msg = array();
    	$logid = 0;
    	$log = '';
    	$string = '';
    	
    	$arr = explode('=', $param);
    	$key = isset($arr[0]) ? $arr[0] : 'error';
    	$alarm = isset($arr[1]) ? $arr[1] : 0;
    	
    	$cmd  = '/reportapi/accesslog/NumberOfNode';
		$return = Ym_CommonTool::phpCli($cmd, FALSE, TRUE);
		
		$data = json_decode($return,TRUE);
		
		//$nodestr = Ym_Config::getAppItem("monitor:mongo.accesslog.tables");
		//$nodes = explode(',', $nodestr);

		$i = 0;
		foreach($data['data']['series'] as $k=>$v){
			
			if ($i<3 AND $v<=$alarm) {
				
				$code = 2;
			}
			
			$string .= $k.' : '.$v.'<br/>';
			$i++;
		}
		$string .= 'alarm_num:'.$alarm;
		
		$thealarm  = 'Time:'.date('Y/m/d H:i:s',$currentTime);
		$thealarm .= ' Code:'.$code;
		$thealarm .= ' IP:'.long2ip($ip);
		$thealarm .= ' App:'.$app;
		$thealarm .= ' Msg:"'.$string;
		Ym_Logger::info(strip_tags($thealarm));
		
    	return array(
    		"time"	=>	$currentTime,
    		"code"	=>	$code,
    		"ip"	=>  $ip,
    		"app"	=>  $app,
    		"msg"	=>	$string,
    		"logid" => 	$logid,
    		"log" 	=> 	$log
    	);
	}
	    
}