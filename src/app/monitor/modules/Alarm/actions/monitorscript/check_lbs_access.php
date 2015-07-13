<?php

/**
 * The main monitorscript of checkout
 * 
 * Check_lbs_access is monitoring the requests of any region such as sin at current minute
 * More scripts Referer to conf/app/yeahmonitor/actions.ini
 * 
 * @author		young<young.zhang@yeahmobi.com>
 * @since		2014-08-10
 * @version		V2.0.1
 * 
 */
class Action_Check_lbs_access extends Yaf_Action_Abstract
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
	public function plugins($var=array()) {
		
		$accessModel = new Service_Log_Persistent();
		$record    	 = $accessModel->getRecordTime('accesslog',1);
		return $record-60;
		/*if ($record AND $var['currentTime']>$dataTime) {
			Ym_Logger::info('check time: '.$var['currentTime'].' grant and equal than data time: '.$dataTime);
		}*/
	}
	
	/**
	 * 
	 * 检测脚本主要程序（只需要编写该方法即可）
	 */
	public function getCheckResult($monitorConfig,$lastTime,$currentTime) {
		
		if (empty($monitorConfig)) Ym_CommonTool::myoutput(FALSE, 'get monitor data error or empty!');
		
		$serviceId 	= isset($monitorConfig['id']) ? $monitorConfig['id'] : '';
		$log_app_id = isset($monitorConfig['monitor_app']) ? $monitorConfig['monitor_app'] : '';
		$ip    		= isset($monitorConfig['monitor_ip']) ? $monitorConfig['monitor_ip'] : '';
		$app   		= isset($monitorConfig['app_name']) ? $monitorConfig['app_name'] : 'system';	
		$service   	= isset($monitorConfig['monitor_service']) ? $monitorConfig['monitor_service'] : '';	
		$param 		= isset($monitorConfig['monitor_param']) ? $monitorConfig['monitor_param'] : '';
		
		
		$maxTime = $this->plugins(array('currentTime'=> $currentTime,'app'=>$app));
		$checkTime = strtotime(date('Y-m-d H:i',$maxTime));
		if ($checkTime<=$lastTime) {
			Ym_Logger::debug('weblog.'.$service.' checktime: '.$checkTime.' <= Last: '.$lastTime.', pass!*');
			$checkTime = strtotime(date('Y-m-d H:i',$currentTime));
		}
		
		
		$model = new Model_Alarm_Monitorscript;
		
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
    	
    	$return1 = $model->checkLbsAccess($checkTime-60);
    	$return2 = $model->checkLbsAccess($checkTime);
    	$num1 = ($return1['num']) ? $return1['num'] : 0;
    	$num2 = ($return2['num']) ? $return2['num'] : 0;
		$currentdiff = 0;
		$go = 'fall';$flug = '↓';
		$color = 'green';
		
    	
    	if ($num1 <= $num2) {$go = 'rise';$flug = '↑';} //上涨了
    	if ($num1 != 0) $currentdiff = round((abs($num1 - $num2) / $num1) * 100, 2);
    	
    	if (isset($alarm_arr['c']) AND ($currentdiff >=  $alarm_arr['c'])) {
    	
    		$code = 2;
    		$color = 'red';
    		
    	}
		elseif (isset($alarm_arr['w']) AND ($currentdiff >=  $alarm_arr['w']) AND ($currentdiff <=  $alarm_arr['c'])) {
    	
    		$code = 1;
    		$color = '#f89406';
    	}

    	$string .= date('H:i',$checkTime-60).' = '.$num1.'<br/>';
    	$string .= date('H:i',$checkTime).' = '.$num2.'<br/>';
    	$string .= $go.' <font color="'.$color.'">'.$currentdiff.'% '.$flug.'</font><br/>';
    	$string .= 'warning:'.$alarm_arr['w'].'% critical:'.$alarm_arr['c'].'%';
		
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