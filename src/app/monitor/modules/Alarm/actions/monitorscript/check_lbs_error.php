<?php

/**
 * The main monitorscript of checkout
 * 
 * Check_lbs_now is monitoring the status (>=500) of any lbs at current minute
 * More scripts Referer to conf/app/yeahmonitor/actions.ini
 * 
 * @author		young<young.zhang@yeahmobi.com>
 * @since		2014-08-10
 * @version		V2.0.1
 * 
 */
class Action_Check_lbs_error extends Yaf_Action_Abstract
{
	
	public function execute() {

    	$this->Model = new Model_Alarm_Monitorcheck;
    	
    	$O = $this->Model->go($this->getRequest()->getParams());
    	$this->plugins(array('currentTime'=>$O->currentTime)); //当检测时间大于数据记录时间时，直接退出
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
    	$color = 'green';
    	$num = 0;
    	
		//parse the params
    	$alarm_arr = array();
    	$param_arr = explode("\n", $param);
    	foreach ($param_arr as $v) {
    	
    		$arr = explode('=', $v);
    		$key = isset($arr[0]) ? $arr[0] : 'error';
    		$alarm = isset($arr[1]) ? $arr[1] : 0;
    		$alarm_arr[$key] = $alarm;
    	}
    	
    	$return = $model->checkLbsError($checkTime);
		
    	if ($return['num'] AND $return['num'] >= $alarm_arr['c']) {
    	
    		$code = 2;
    		$color = 'red';
    	} 
    	elseif ($return['num'] AND $return['num'] >= $alarm_arr['w'] AND $return['num'] <= $alarm_arr['c']) {
    		
    		$code = 1;
    		$color = '#f89406';
    	}
    	
    	$return['num'] == NULL ? $num = 0 : $num = $return['num'];
    	
    	$string .= date('H:i',$checkTime).' = <font color="'.$color.'">'.$num.'</font> (status>=500)<br/>';
    	if ($code>0) {
    		$detail = $model->checkLbsErrorDetail($checkTime);
    		if($detail) {
    		
    			foreach ($detail as $v) {
    				$string .= $v['host'].' '.$v['path'].' '.$v['status'].' '.$v['server'].' = '.$v['num'].'<br/>';
    			}
    		}
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