<?php

/**
 * The main process of monitoring as the base class of the monitorscripts
 * 
 */
class Model_Alarm_Monitorcheck
{
	
	public $alarmModel = NULL;
	public $serviceId = 0;
	public $lastStatus = 0;
	public $lastTime = 0;
	public $currentTime = 0;
	public $lastNotifyTime = 0;
	public $enableNotify = 0;
	
	public $monitorConfig = array();
	
    public function go($params) {

    	$this->parseParams($params);
    	$this->alarmModel = new Model_Alarm_Monitorcore;
    	$this->monitorConfig = $this->getMonitorConfig($this->serviceId);
    	return $this;
    }
    
    public function parseParams($params=array()) {
    	
        $this->serviceId    = isset($params['id']) ? $params['id'] : 0;
        $this->lastStatus  	= isset($params['lastStatus']) ? $params['lastStatus'] : 0;
        $this->lastTime	 	= isset($params['lastTime']) ? $params['lastTime'] : 0;
        $this->currentTime 	= isset($params['currentTime']) ? $params['currentTime'] : 0;
        $this->lastNotifyTime = isset($params['lastNotifyTime']) ? $params['lastNotifyTime'] : 0;
        
        if ($this->serviceId==0 OR $this->lastTime == 0 OR $this->currentTime == 0) {
        	
        	Ym_CommonTool::myoutput(FALSE,'doMonitor params is error!');
        	return FALSE;
        }
    }

    public function doMonitor($checkResult=array()) {

        $enableNotify = $this->ifNotify();
        
		$updateR = $this->updateCheckResult($checkResult, $this->monitorConfig, $this->lastStatus, $enableNotify);

		$serviceId 	= isset($this->monitorConfig['id']) ? $this->monitorConfig['id'] : 0;
		if ($enableNotify)
			$r = $this->doNotify($checkResult, $this->monitorConfig, $this->lastStatus);
		else 	
			Ym_CommonTool::myoutput(FALSE, 'ID: '.$serviceId.' DisableNotify ');
	}
	
	public function getMonitorConfig($id) {
		
		return $this->alarmModel->{__FUNCTION__}($id);
	}

	public function updateCheckResult($checkResult='', $monitorData=array(), $lastStatus=0, $enableNotify=1) {
	
		$updateModel = new Model_Alarm_Monitorupdate;
		return $updateModel->{__FUNCTION__}($checkResult, $monitorData, $lastStatus, $enableNotify);
	}
	
	public function ifNotify() {
		
		$enableNotify = 0;
		
		$arr = array('notify_status','notify_method','notify_type');
		foreach ($arr as $v) {
			if (isset($this->monitorConfig[$v]) AND $this->monitorConfig[$v]) {
				$enableNotify = 1;
			} else {
				return $this->enableNotify = 0;
			}
		}
		
		if (!$this->monitorConfig['notify_userid'] AND !$this->monitorConfig['notify_groupid']) {
			return $this->enableNotify = 0;
		}
		
        if ( $this->lastNotifyTime == 0  OR $this->monitorConfig['notify_interval'] == 0) $enableNotify = 1;
        
        if ( $this->lastNotifyTime ) {
        	$notifyTime = $this->monitorConfig['notify_interval'] * 60 + strtotime(date('Y-m-d H:i',$this->lastNotifyTime));
        	if ( time() >= $notifyTime  ) $enableNotify = 1;
        	//Ym_Logger::info('=======notifyTime:'.$notifyTime.'#last:'.$this->lastNotifyTime.'#enableNotify:'.$enableNotify);
        }
        
        return $this->enableNotify = $enableNotify;
	}
	
	public function doNotify($checkResult, $monitorConfig, $lastStatus) {
		
		$model = new Model_Alarm_Monitornotify;
		return $model->{__FUNCTION__}($checkResult, $monitorConfig, $lastStatus);
	}
	    
}