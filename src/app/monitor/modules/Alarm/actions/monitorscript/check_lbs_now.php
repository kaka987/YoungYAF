<?php

/**
 * The main monitorscript of checkout
 * 
 * Check_lbs_now is monitoring the requests of any lbs at current minute
 * More scripts Referer to conf/app/yeahmonitor/actions.ini
 * 
 * @author		young<young.zhang@yeahmobi.com>
 * @since		2014-08-10
 * @version		V2.0.1
 * 
 */
class Action_Check_lbs_now extends Yaf_Action_Abstract
{
	
    public function execute() {

    	$this->Model = new Model_Alarm_Monitorcheck;
    	
    	$O = $this->Model->go($this->getRequest()->getParams());
    	$checkResult = $this->getCheckResult($O->monitorConfig, $O->lastTime, $O->currentTime);
    	if ($checkResult) $O->doMonitor($checkResult);
    	
        return FALSE;
    }
    
	/**
	 * 
	 * 检测脚本钩子程序，每次执行该检测脚本可以调用
	 */
	public function plugins($var=array()) {}
	
	/**
	 * 
	 * 检测脚本主要程序（只需要编写该方法即可）
	 */
	public function getCheckResult($monitorConfig,$lastTime,$currentTime) {
		
		if (empty($monitorConfig)) Ym_CommonTool::myoutput(FALSE, 'get monitor data error or empty!');
		
		//$this->plugins($monitorConfig);
		
		$model = new Model_Alarm_Monitorscript;
		
		$serviceId  = isset($monitorConfig['id']) ? $monitorConfig['id'] : '';
		$log_app_id = isset($monitorConfig['monitor_app']) ? $monitorConfig['monitor_app'] : '';
		$ip    		= isset($monitorConfig['monitor_ip']) ? $monitorConfig['monitor_ip'] : '';
		$app   		= isset($monitorConfig['app_name']) ? $monitorConfig['app_name'] : 'system';	
		$param 		= isset($monitorConfig['monitor_param']) ? $monitorConfig['monitor_param'] : '';
		$checkTime 	= strtotime(date('Y-m-d H:i',$currentTime));
		
    	$code = 0;
    	$maxTime = 0;
    	$msg = array();
    	$logid = 0;
    	$log = '';
    	$string = '';
    	
		//parse the params
    	$alarm_arr = array();
    	$param_arr = explode("\n", $param);
    	foreach ($param_arr as $v) {
    	
    		$arr = explode('=', $v);
    		$key = isset($arr[0]) ? $arr[0] : 'error';
    		$alarm = isset($arr[1]) ? $arr[1] : 0;
    		$alarm_arr[$key] = $alarm;
    	}
    	
    	$cmd  = '/reportapi/accesslog/NumberOfNode';
		$return = Ym_CommonTool::phpCli($cmd, FALSE, TRUE);
		$data = json_decode($return,TRUE);
		
		foreach($data['data']['series'] as $k=>$v){
			
			if (isset($alarm_arr['n']) AND ($k == $alarm_arr['n'])) continue;
			
			if ( $v <= $alarm_arr['c']) {
				$code = 2;
				$string .= $k.' : <font color="red">'.$v.'</font><br/>';
				continue;
			}
			
			if ( $v <= $alarm_arr['w']) {
				$code = 1;
				$string .= $k.' : <font color="#f89406">'.$v.'</font><br/>';
				continue;
			}
			
			$string .= $k.' : <font color="green">'.$v.'</font><br/>';
		}
		$string .= 'warning:'.$alarm_arr['w'].' critical:'.$alarm_arr['c'];
		
		$thealarm  = ' Id:'.$serviceId;
		$thealarm .= ' Time:'.date('Y/m/d H:i:s',$checkTime);
		$thealarm .= ' Code:'.$code;
		$thealarm .= ' IP:'.long2ip($ip);
		$thealarm .= ' App:'.$app;
		$thealarm .= ' Msg:"'.$string;
		Ym_Logger::debug(strip_tags($thealarm));
		
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