<?php

/**
 * The main process of monitoring
 * 
 */
class Action_Monitorexec extends Yaf_Action_Abstract
{
	
	public $alarmModel = NULL;
	public $model = NULL;
	
    public function execute() {

    	$this->init();
    	$this->doMonitor();
        return FALSE;
    }
    
    public function init() {
    	
    	$this->model = new Model_Alarm_Monitorcore;
    	$this->alarmModel = new Model_Alarm_Monitorcore;
    }

    public function doMonitor() {
		
		$params      = $this->getRequest()->getParams();
        $id          = isset($params['id']) ? $params['id'] : 0;
        $lastStatus  = isset($params['status']) ? $params['status'] : 0;
        
		$monitorData = $this->getMonitorData($id); //data from monitor_config
		
		$checkResult = $this->getCheckResult($monitorData);
		$updateR = $this->updateCheckResult($checkResult, $monitorData, $lastStatus);
		
		if (isset($monitorData['notify_status']) AND $monitorData['notify_status']) {
			
			$r = $this->doNotify($monitorData, $lastStatus);
		}
		
		return FALSE;//no view
	}

	public function getCheckResult($monitorData) {
		
		if (empty($monitorData)) self::output(FALSE, 'get monitor data error or empty!');
		
		$url   = isset($monitorData['monitor_url']) ? $monitorData['monitor_url'] : '';
		$param = isset($monitorData['monitor_param']) ? explode('\n', $monitorData['monitor_param']) : array();
		
		return $this->phpCurl($url, $param);
	}
	
	public function getMonitorData($id) {
		
		return $this->alarmModel->{__FUNCTION__}($id);
	}
    
    private function output($flag=TRUE, $msg='', $data=array()) {
    	
    	if ($flag) {
    		$out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
    	} else {
    		$out = array('flag' => 'error', 'msg' => $msg, 'data' => $data);
    		Ym_Logger::error($msg);
    	}
    	
    	Ym_CommonTool::output($this, $out, 'json');
    }
}