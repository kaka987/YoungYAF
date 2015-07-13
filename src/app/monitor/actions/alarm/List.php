<?php
class Action_List extends Controller_Application {

    protected $layout = FALSE;

    protected $isNeedLogin = User_Session::LOGIN_WITHOUT;

    public function run() {
    	
    	$this->display = 'none';
    	date_default_timezone_set('Asia/Shanghai');
    	$this->alarmModel = new Model_Alarm_Monitorcore;
    	
    	$queryData = $this->getRequest()->getQuery();
    	
    	if ( isset($queryData['q']) AND $queryData['q'] == 'getlog') {
    		
    		$id = isset($queryData['id']) ? $queryData['id'] : 0;
    		$log = $this->getLog($id);
    		
    		echo ($log) ?  $log : 'N/A';
    		return FALSE;
    	}

    	if (!isset($queryData['q']) OR $queryData['q']!='list') return FALSE;
    	
    	$page = isset($queryData['page']) ? $queryData['page'] : 1;
    	$limit = 20;
    	
    	$level = isset($queryData['level']) ? $queryData['level'] : 'a';
    	
		$listData  = $this->alarmModel->getListFromAlarm($page-1,$limit,$level);
		$listCount = $this->alarmModel->getListFromAlarm(-1,0,$level);
		$pageNum   = ceil($listCount['num']/$limit);
		
		$notify_status_arr = array('disable','enable');
		$status_arr = array('ok','warning','critical','unkown');
		$listed = array();
		foreach ($listData as $k=>$list) {
			
			$notify_status = $list['notify_status'] ? $list['notify_status'] : 0;
			$status = $list['status'] ? $list['status'] : 0;
			$lastTime = $list['last_check_time'] ? $list['last_check_time'] : 0;
			$nextTime = $list['next_check_time'] ? $list['next_check_time'] : 0;
			$notifyInterval = $list['notify_interval'] ? $list['notify_interval'] : 0;
			$lastNotifyTime = $list['last_notify_time'] ? $list['last_notify_time'] : 0;
			$nextNotifyTime = 0;
			if($lastNotifyTime) {
				$nextNotifyTime = $notifyInterval * 60 + $lastNotifyTime;
			} else {
				$nextNotifyTime = $nextTime;
			}
			if ($notify_status == 0) $nextNotifyTime = 0;
			
			$status_change_time = $list['status_change_time'] ? $list['status_change_time'] : time();
			
			$listed[$k]['service_id'] = $list['service_id'];
			$listed[$k]['service_name'] = $list['monitor_service'];
			$listed[$k]['notify_status'] = $notify_status_arr[$notify_status];
			$listed[$k]['status'] = $status_arr[$status];
			$listed[$k]['lastTime'] = $lastTime ? date('Y/m/d H:i:s', $lastTime) : 'N/A';
			$listed[$k]['nextTime'] = $nextTime ? date('Y/m/d H:i:s', $nextTime) : 'N/A';
			$listed[$k]['last_notify_time'] = $nextNotifyTime ? date('Y/m/d H:i:s', $nextNotifyTime) : 'N/A';
			$listed[$k]['time'] = $this->getLastTime($status_change_time);
			$listed[$k]['status_detail'] = $list['status_detail'];
		}

        if(!empty($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }

		echo json_encode(array("data"=>$listed,"page_num"=>$pageNum));
    	
    }
    
    public function getLog($id=0) {
    	
    	if ($id==0) return FALSE;
    	
    	$log = $this->alarmModel->getLog($id);
    	
    	return $log['log'] ? $log['log'] : FALSE;
    	
    }
    
    public function getLastTime($lastTime=NULL) {
    
    	if ($lastTime>=time()) return 0;
    	$cha = time()-$lastTime;
    	if ($cha>0) {
    		$day	= floor($cha/86400);         
    		$hour	= floor(($cha%86400)/3600);                   
    		$minute	= floor(($cha%3600)/60);         
    		$second	= floor(($cha%60));
    	}
    	
    	return $day.'d '.$hour.'h '.$minute.'m '.$second.'s';
    }

    private function loadModel() {
    	 
    	return new Model_Alarm_Config();
    }
}