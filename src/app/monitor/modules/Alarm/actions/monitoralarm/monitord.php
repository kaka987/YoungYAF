<?php

/**
 * The main process of monitoring
 * 
 */
class Action_Monitord extends Yaf_Action_Abstract
{
	
	public $alarmModel = NULL;
	public $model = NULL;
	
    public function execute() {

    	$this->init();
    	$this->doMonitorAsJobs();
    	
        return FALSE;
    }
    
    public function init() {
    	
    	$this->alarmModel = new Model_Alarm_Monitorcore;
    }

	public function doMonitorAsJobs() {
		
		$canCheckTime = time() - 60;
		$ids = $this->getCheckServiceIds( $canCheckTime );
		$cmd = '';
		if ($ids) {
			Ym_Logger::info('Start monitor check:-----------------------------------------------------');
			foreach ($ids as $id) {
				
				$status = isset($id['status']) ? $id['status'] : 3;//3默认为unknown
				$theTime = $id['next_check_time'] ? $id['next_check_time'] : time();
				$lastTime = $id['last_check_time'] ? $id['last_check_time'] : $theTime-60;
				$lastNotifyTime = $id['last_notify_time'] ? $id['last_notify_time'] : 0;
				$monitorApi = $id['monitor_api'] ? $id['monitor_api'] : '';
				
	    		if ( ! $monitorApi) {
	    			Ym_Logger::error('Unknow monitor api on service_id : '.$id["service_id"].'!');
	    			continue;
	    		}
	    		
				$cmd  = '/alarm/monitorscript/'.$monitorApi.'/id/';
				$cmd .= $id['service_id'].'/lastStatus/'.$status;
				$cmd .= '/lastTime/'.$lastTime.'/currentTime/'.$theTime.'/lastNotifyTime/'.$lastNotifyTime;
				Ym_CommonTool::phpCli($cmd, TRUE, FALSE);
			}
			Ym_Logger::info("End monitor check:-----------------------------------------------------");
		}
	
		return FALSE;
	}

	public function getCheckServiceIds($time=NULL) {
		
		return $this->alarmModel->{__FUNCTION__}($time);
	}
    
}