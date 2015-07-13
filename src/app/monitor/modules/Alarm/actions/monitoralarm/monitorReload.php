<?php

/**
 * 
 * 监控进程启动时，根据监控配置，更新监控服务的
 * @author young<young@yeahmobi.com>
 *
 */
class Action_Reload extends Yaf_Action_Abstract
{
    public function execute()
    {

    	$this->init();
    	
    	$monitorAlarmData = $this->getDataFromMonitorconfig();
    	$monitorAlarmedData = $this->getDataFromMonitorAlarm();
    	
    	$theNewAlarmData = $this->getTheNewAlarmData($monitorAlarmData, $monitorAlarmedData);
    	
    	$this->doUpdateData($theNewAlarmData);
    	
        return FALSE;
    }
    
    public function init() {
    	
    	$this->model = new Model_Alarm_Monitorcore;
    }
    
    public function getDataFromMonitorConfig() {
    	
    	$r = $this->model->{__FUNCTION__}();
    	return $this->doIds($r,'id');
    }
    
	public function getDataFromMonitorAlarm() {
    	
    	$r = $this->model->{__FUNCTION__}();
    	return $this->doIds($r,'service_id');
    }
    
    public function doIds($r = array(), $key='id') {
    	
    	$ids = array();
    	if (empty($r)) return $ids;
    	if ($r) {
    		foreach($r as $id) {
    			$ids[] = $id[$key];
    		}
    	}
    	return $ids;
    }
    
    public function getTheNewAlarmData($configdata, $rundata) {
    	
    	$alarmData = array('insert'=>array(), 'delete' => array());
    	
    	$alarmData['insert'] = $this->getInsertData($configdata, $rundata);
    	$alarmData['delete'] = $this->getDeleteData($configdata, $rundata);
    	
    	return $alarmData;
    }
    
    public function getDeleteData($configdata, $rundata) {
    	
    	return array_diff($rundata, $configdata);
    }
    
    public function getInsertData($configdata, $rundata) {
    	
    	return array_diff($configdata, $rundata);
    }
    
    public function doUpdateData($data) {
    	
    	if (isset($data['insert']) AND $data['insert']) {
    		
    		$r = $this->model->insertData($data['insert']);
    		if ( ! $r) self::output(FALSE, 'insert into monitor_alarm error!');
    	}
    	
    	if (isset($data['delete']) AND $data['delete']) {
    		
    		$r = $this->model->deleteData($data['delete']);
    		if ( ! $r) self::output(FALSE, 'delete from monitor_alarm error!');
    	}
    	
    	$r = $this->model->updateTime();
    	if ( ! $r) self::output(FALSE, 'updateTime from monitor_alarm error!');
    	
    	self::output(TRUE, 'OK');
    }
    
    private function output($flag=TRUE, $msg='', $data=array()) {
    	
    	if ($flag) {
    		$out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
    	} else {
    		$out = array('flag' => 'error', 'msg' => $msg, 'data' => $data);
    		Ym_Logger::error($msg);
    	}
    	
    	Ym_CommonTool::output($this, $out, 'json');
    	exit;
    }
}