<?php

/**
 * The main process of monitoring
 * 
 */
class Action_MonitorCheckout extends Yaf_Action_Abstract
{
	
	public $alarmModel = NULL;
	public $model = NULL;
	
    public function execute() {

    	$this->init();
    	
    	$params      = $this->getRequest()->getParams();
        
        $this->getCheckResult($params);
        return FALSE;
    }
    
    public function init() {
    	
    	$this->model = new Model_Alarm_Monitorcore;
    }
    
    public function getCheckResult($params) {
        //$config = Yaf_Registry::get("monitor_config");
        //$stepTime  = intval($config['loghandle']['weblog']['step']);

    	$checkParams = explode('#', $params['checkparam']);

    	$out = array();
    	$code = 0;
    	$maxTime = 0;
    	if (count($checkParams)>0) {
    		
/*    		$maxTime = $this->model->getCheckMaxTime();
    		if ($maxTime['time'] AND $params['theTime']>=$maxTime['time']) {
    			echo 0;
    			return FALSE;
    		}*/
    		foreach ($checkParams as $v) {
    			
    			$arr = explode('=', $v);
    			$key = isset($arr[0]) ? $arr[0] : 'error';
    			$alarm = isset($arr[1]) ? $arr[1] : 100;
    			$return = $this->model->{__FUNCTION__}($key, $params['log_app_id'], $params['lastTime'], $params['theTime']);
    			
    			if ($return['num']>=$alarm) {
    				
    				$msg[] = array(
    							"key" => $key,
    							"num" => $return['num'],
    							"alarm" => $alarm,
    							"logid" => $return['content_id'],
    							"log"	=> $return['sample']
    					);
    				$code = 1;
    				
    			} else {
    				
    				$num = isset($return['num']) AND $return['num'] ? $return['num'] : 0;
    				$msg[] = array(
    					"key"=>$key,
    					"num"=>(int)$num,
    					"alarm"=>$alarm
    				);
    			}
    		}
    		
    		
    		$out = array("time"=>$params['theTime'],"code"=>$code,"msg"=>$msg);
    	}
    	
    	if (count($out)>0) echo json_encode($out);
    	else echo json_encode($params['theTime'],0,'ok');
    }
    
}