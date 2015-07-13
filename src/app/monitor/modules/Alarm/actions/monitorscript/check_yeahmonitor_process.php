<?php

/**
 * The main monitorscript of checkout
 * 
 * Check_yeahmonitor_process is monitoring the own monitor server,such as load,mem,disk,process
 * More scripts Referer to conf/app/yeahmonitor/actions.ini
 * 
 * @author		young<young.zhang@yeahmobi.com>
 * @since		2014-08-10
 * @version		V2.0.1
 * 
 */
class Action_Check_yeahmonitor_process extends Yaf_Action_Abstract
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
		
		$serviceId 	= isset($monitorConfig['id']) ? $monitorConfig['id'] : '';
		$log_app_id = isset($monitorConfig['monitor_app']) ? $monitorConfig['monitor_app'] : '';
		$ip    		= isset($monitorConfig['monitor_ip']) ? $monitorConfig['monitor_ip'] : '';
		$app   		= isset($monitorConfig['app_name']) ? $monitorConfig['app_name'] : 'system';	
		$service   	= isset($monitorConfig['monitor_service']) ? $monitorConfig['monitor_service'] : '';	
		$param 		= isset($monitorConfig['monitor_param']) ? $monitorConfig['monitor_param'] : '';
		$checkTime = strtotime(date('Y-m-d H:i',$currentTime));
		
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
    	
    	
    	//cpu load
    	$cpuLoad = `cat /proc/loadavg`;
    	$mem = `free -m | grep Mem |awk -F' ' '{print $4}'`;
    	$disk = `df -h | awk -F' ' '{print$5}' | grep -v Use |xargs`;
    	$accessprocess = `ps -ef | grep 'accesslog' | grep -v grep | wc -l`;
    	$weblogprocess = `ps -ef | grep 'weblog' | grep -v grep | wc -l`;
  
    	$memalarm = isset($alarm_arr['mem']) ? $alarm_arr['mem'] : 100;
    	if ($mem) {
    	
    		
    		if ((int)$mem<=$memalarm) $code = 1;
    	}
    	
    	$cpualarm = isset($alarm_arr['cpu']) ? $alarm_arr['cpu'] : 10;
    	if ($cpuLoad) {
    		
    		$d = explode(' ', $cpuLoad);
    		if ((int)$d[0]>$cpualarm) $code = 2;
    	}
    	
		$diskalarm = isset($alarm_arr['disk']) ? $alarm_arr['disk'] : 80;
    	if ($disk) {
    	
    		foreach (explode(' ', $disk) as $v) {
    			
    			if ((int)trim($v,'%')>$diskalarm) $code = 2;
    		}
    	}
    	
    	$processAlarm = isset($alarm_arr['accessnum']) ? $alarm_arr['accessnum'] : 20;
    	if ($accessprocess) {
    	
    		if (((int)$accessprocess >= $processAlarm)  OR ((int)$accessprocess == 0)) $code = 2;
    	}
    	
		$processAlarm1 = isset($alarm_arr['weblognum']) ? $alarm_arr['weblognum'] : 20;
    	if ($weblogprocess) {
    	
    		if (((int)$weblogprocess >= $processAlarm1)  OR ((int)$weblogprocess == 0)) $code = 2;
    	}

    	$string .= 'cpuload:'.$cpuLoad.' alarm:'.$cpualarm.'<br/>';
		$string .= 'mem:'.$mem.' alarm:'.$memalarm.'<br/>';
		$string .= 'disk:'.$disk.' alarm:'.$diskalarm.'<br/>';
		$string .= 'accessnum:'.$accessprocess.' alarm:'.$processAlarm.'<br/>';
		$string .= 'weblognum:'.$weblogprocess.' alarm:'.$processAlarm1.'<br/>';
		
		$thealarm  = ' Id:'.$serviceId;
		$thealarm .= ' Time:'.date('Y/m/d H:i:s',$checkTime);
		$thealarm .= ' Code:'.$code;
		$thealarm .= ' IP:'.long2ip($ip);
		$thealarm .= ' App:'.$app;
		$thealarm .= ' Msg:"'.$string;
		Ym_Logger::debug($thealarm);
		
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