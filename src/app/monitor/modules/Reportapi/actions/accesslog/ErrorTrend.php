<?php

class Action_errorTrend extends Controller_Application
{

	public $code = array(200,400,403,404,499,500,502,504,505);

	public function run() {
		
		$model       = new Model_ReportApi_Accesslog();
		$hosts = $model->getBusiness();
		$from  = strtotime($this->getRequest()->getQuery('from', date('Y-m-d 00:00:00')));
		$to    = $from + 86400;
		$where = "WHERE `time` >= {$from}";
		if ($from && $from != strtotime(date('Y-m-d 00:00:00'))) {
			$where = "WHERE `time` >= {$from} AND `time`< {$to} ";
		}
		
		$codestr = implode(',', $this->code);
        $where .= " AND status in (".$codestr.")";
		$where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";
		$res    = $model->getErrorTrend($where);
		$data   = $datatmp = array();
		$minute = $tmpminute = 0;
		foreach ($res as $val) {

			$status = $val['status'];
			$minute = $val['time'];

			isset($data[$minute][$status]['num']) ? $data[$minute][$status]['num'] += $val['num'] : $data[$minute][$status]['num'] = $val['num'];
			$data[$minute][$status]['top'][$val['num']] = array($val['host'],$val['path'],$val['num']);

			if ($tmpminute != 0 AND $tmpminute < $minute) {

				foreach ($this->code as $v) {
					
					$num = isset($data[$tmpminute][$v]['num']) ? $data[$tmpminute][$v]['num'] : 0;
					
					$t = isset($data[$tmpminute][$v]['top']) ? $data[$tmpminute][$v]['top'] : array();
					krsort($t);
					$top = array_slice($t,0,3);
					
					$datatmp[$v][] = array("time"=>$tmpminute, "num"=>$num, "top"=>$top);
					unset($t,$top);
				}
			}
				
			$tmpminute = $minute;
		}
		$result['series'] = $datatmp;

		if($data){
			static::output(1,'errorTrend',$result);
		}else{
			static::output(0,'no data', array());
		}
		//return $result;
	}
}