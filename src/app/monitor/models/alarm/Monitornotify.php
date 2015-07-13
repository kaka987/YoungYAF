<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Monitornotify
{

	public $currentType = NULL;//错误级别
	private  $_C = array('sms'=>array(),'email'=>array());
	
	/**
	 * 访问初始化
	 */
	public function __construct() {
		
		$this->dao = new Ym_Dao('default');
		$this->monitorUserGroupsTable   = Sys_Database::getTable('user_groups');
        $this->monitorUserTable   		= Sys_Database::getTable('user');
        
        $this->t = microtime(TRUE);
        //echo $this->t.PHP_EOL;
	}
	
	public function __destruct() {
		/*echo microtime(TRUE).PHP_EOL;
		echo microtime(TRUE)-$this->t;
		echo PHP_EOL;*/
	}
	
	public function doNotify($checkResult, $monitorConf, $lastStatus) {
		
		$service 		= isset($monitorConf['monitor_service']) ? $monitorConf['monitor_service'] : '';
		$code 			= isset($checkResult['code']) ? $checkResult['code'] : 0;
		$contacts 		= $this->getContacts($checkResult, $monitorConf, $lastStatus);
		
		// Get sending content
		$time  = isset($checkResult['time']) ? date('Y-m-d H:i:s',$checkResult['time']) : date('Y-m-d H:i:s', time());
		$data = array(
			'ip'		=> long2ip($checkResult['ip']),
			'app'		=> $checkResult['app'],
			'service'	=> $service,
			'notifyType'=> $this->currentType,
			'time'		=> $time,
			'message'	=> $checkResult['msg'],
			'logid'		=> ($checkResult['logid']) ? $checkResult['logid'] : 'N/A',
			'log'		=> ($checkResult['log']) ? $checkResult['log'] : 'N/A'
		);
		$body  = $this->getBody($data);

		foreach($contacts as $method=>$c) {

			switch ($method) {
				case 'email' :
					
					if (empty($c)) break;
					$emailList = $this->getEmailList($c);
					
					$title = $this->getTitle($data,'email');
					//$this->sendByEmail($emailList, $title, $body, long2ip($checkResult['ip']) , $checkResult['app']);
					break;
					
				case 'sms' :
					if (empty($c)) break;
					$phoneList = $this->getPhoneList($c);
					
					$title = $this->getTitle($data,'sms');
					if ($code>0 AND ($lastStatus == $code)) {
						Ym_Logger::info('no change,no sendding sms:'.$title.'#'.$checkResult['app']);
						break;
					}
					
					foreach($phoneList as $phone) {
						//$this->sendBySms($phone['phone'], $title, $body, long2ip($checkResult['ip']) , $checkResult['app']);
					}
					break;
					
				default:
					//$this->sendByEmail($notify_contact, $title, $body);
					break;
			}
		}
	}
	
	/**
	 * 
	 * 获取该条报警的接收人及接收方式
	 * 
	 * @return array('email'=>array(),'phone'=>array())
	 * @param array $checkResult
	 * @param array $monitorConf
	 * @param int $lastStatus
	 */
	public function getContacts($checkResult, $monitorConf, $lastStatus) {
		
		$notify_type    = isset($monitorConf['notify_type']) ? explode(',', $monitorConf['notify_type']) : array();
		$code 			= isset($checkResult['code']) ? $checkResult['code'] : 0;
		$notify_method  = isset($monitorConf['notify_method']) ? explode(',', $monitorConf['notify_method']) : array();
		$notify_period  = isset($monitorConf['notify_period']) ? $monitorConf['notify_period'] : '';
		$notify_userid  = isset($monitorConf['notify_userid']) ? $monitorConf['notify_userid'] : 0;
		$notify_groupid = isset($monitorConf['notify_groupid']) ? $monitorConf['notify_groupid'] : 0;
		$serviceId 	    = isset($monitorConf['id']) ? $monitorConf['id'] : 0;
		
		$currentMethod  = '';
		
		if (!$this->getNotifyType($notify_type, $code, $lastStatus)) return FALSE;
		if (!($smethod = $this->getnotifyMethod($notify_method))) return FALSE;
		if (!$this->getNotifyPeriod($notify_period)) return FALSE;

		$group = $this->getGroups($notify_groupid);
		if ($group) {
			foreach($group as $g) {
				if (!$g['notify_status']) { Ym_Logger::error('GID: '.$notify_groupid.' DisableNotify'); continue; }
				if (!$this->getNotifyType(explode(',', $g['notify_type']), $code, $lastStatus)) continue;
				$gmethod = $this->getnotifyMethod(explode(',', $g['notify_method']));
				if (!$gmethod) continue;
				if ($smethod==3) {
					$currentMethod = $gmethod;
				} else {
					if ($gmethod<3 AND $gmethod!=$smethod) continue;
					$currentMethod = $smethod;
				}
				if (!$this->getNotifyPeriod($g['notify_period'])) continue;
				
				$filter = 0;
				if ($g['host_id'] AND in_array(long2ip($checkResult['ip']), explode(',', $g['host_id']))) $filter=1;
				if ($filter==0 AND $g['apps'] AND in_array($checkResult['app'], explode(',', $g['apps']))) $filter=1;
				if ($filter==0 AND $g['keyword']) {
					$keywords = explode("\n", $g['keyword']);
					foreach ($keywords as $k) {
						if (strpos($checkResult['msg'], $k) !== FALSE) $filter=1;
					}
				}
				if (!$filter) continue;
				
				$this->filterUser($checkResult,$lastStatus,$currentMethod,$g['user_id']);
				
			}
		} else { Ym_Logger::info(date('Y-m-d H:i:s',$checkResult['time']).':No Groups'); }
		
		if ($notify_userid) {
			
			$this->filterUser($checkResult,$lastStatus,$smethod,$notify_userid);
		} else { Ym_Logger::info(date('Y-m-d H:i:s',$checkResult['time']).':No Users'); }
		
		return $this->_C;
	}
	
	/**
	 * 
	 * 过滤用户报警配置
	 * 
	 * @param array $checkResult
	 * @param int $lastStatus
	 * @param str $smethod
	 * @param str $userid
	 */
	public function filterUser($checkResult,$lastStatus,$smethod,$userid='') {
	
		$currentMethod 	= '';
		$code 			= isset($checkResult['code']) ? $checkResult['code'] : 0;
		$uidarr 		= $this->getUsers($userid);
		if ($uidarr) {
		
			foreach($uidarr as $u) {
				
				if (!$u['notify_status']) { Ym_Logger::error('UID: '.$userid.' DisableNotify'); continue; }
				if (!$this->getNotifyType(explode(',', $u['notify_type']), $code, $lastStatus)) continue;
				$umethod = $this->getnotifyMethod(explode(',', $u['notify_method']));
				if (!$umethod) continue;
				if ($smethod==3) {
					$currentMethod = $umethod;
				} else {
					if ($umethod<3 AND $umethod!=$smethod) continue;
					$currentMethod = $smethod;
				}
				if (!$this->getNotifyPeriod($u['notify_period'])) continue;
				
				$filter = 0;
				if ($u['host_id'] AND in_array(long2ip($checkResult['ip']), explode(',', $u['host_id']))) $filter=1;
				if ($filter==0 AND $u['apps'] AND in_array($checkResult['app'], explode(',', $u['apps']))) $filter=1;
				if ($filter==0 AND $u['keyword']) {
					$keywords = explode("\n", $u['keyword']);
					foreach ($keywords as $k) {
						if (strpos($checkResult['msg'], $k) !== FALSE) $filter=1;
					}
				}
				if (!$filter) continue;
				
				switch ($currentMethod) {
					case 3:
						$this->_C['email'][] = $u['id'];
						$this->_C['sms'][] = $u['id'];
						break;
					case 2:
						$this->_C['sms'][] = $u['id'];
						break;
					case 1:
						$this->_C['email'][] = $u['id'];
						break;
				}
			}
		}
	}

	public function getGroups($notify_groupid='') {
		if (!$notify_groupid) return FALSE;
		$sql = "select apps,host_id,keyword,user_id,notify_status,notify_method,notify_type,notify_period,notify_interval 
				from ".$this->monitorUserGroupsTable." 
				where id in ($notify_groupid)";
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getUsers($notify_userid='', $uidfromgroup=array()) {
		
		if (!$notify_userid AND empty($uidfromgroup['email']) AND empty($uidfromgroup['sms'])) return FALSE;
		$sql = "select id,apps,host_id,keyword,notify_status,notify_method,notify_type,notify_period,notify_interval 
				from ".$this->monitorUserTable." 
				where id in (".trim($notify_userid,',').")";

		return $this->dao->fetchAll($sql,'', true);
	}

	public function getEmailList($contact=array()) {
	
		$contactstr = implode(',', array_unique($contact));
		$sql = "select email from ".$this->monitorUserTable." where id in(".$contactstr.")";
		$r = $this->dao->fetchAll($sql,'', true);
		$l = '';
		if ($r) {
			foreach ($r as $v) {
				$l .= $v['email'].';';
			}
		}
		
		return rtrim($l,';');
	}
	
	public function getPhoneList($contact=array()) {
	
		$contactstr = implode(',', array_unique($contact));
		$sql = "select phone from ".$this->monitorUserTable." where id in(".$contactstr.")";
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function sendByEmail($emailList, $title, $body, $ip, $app) {

		Ym_Logger::info('Sendmail#'.$emailList);
		Sys_Message::send('mail', $emailList, $title, $body, $ip, $app);
	}
	
	public function sendBySms($to, $title, $body, $ip, $app) {
	
		Ym_Logger::info('Sendsms#'.$to);
		Sys_Message::send('sms', $to, $title, $body, $ip, $app);
	}
	
	public function getNotifyType($notifyType=array(), $thisCode=0, $lastStatus=0) {
		
		$type = array('warning'=>'warning', 'critical'=>'critical', 'unknown'=>'unknown', 'recovery'=>'recovery', 'ok'=>'ok');

		if ($this->currentType === NULL) { 
			switch ($thisCode) {
				case 0:
					if ($lastStatus>0) $this->currentType = $type['recovery'];
					if ($lastStatus==0) $this->currentType = $type['ok'];
					break;
				case 1:
					$this->currentType = $type['warning'];
					break;
				case 2:
					$this->currentType = $type['critical'];
					break;
				case 3:
					$this->currentType = $type['unknown'];
					break;
				default:
					$this->currentType = $type['ok'];
					break;
			}
		}
		
		if (in_array($this->currentType, $notifyType)) return TRUE;
		
		return FALSE;
	}
	
	public function getNotifyPeriod($notify_period='alltime') {
		
		$p = '';
		$week = date('w');
		switch ($week) {
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$p='worktime';
				break;
			case 6:
			case 0:
				$p='noworktime';
				break;
			default:
				$p='alltime';
				break;
		}
		
		if ($notify_period == 'alltime') return TRUE;
		if ($notify_period == $p) return TRUE;
		return FALSE;
	}
	
	public function getnotifyMethod($method=array()) {
		
		if (empty($method)) return FALSE;
		$k1=$k2=0;
		foreach($method as $m) {
			
			if ($m == 'email') $k1 = 1;
			if ($m == 'sms') $k2 = 2;
		}
		return $k1+$k2;
	}
	
	public function getTitle($data=array(), $type='email') {
	
		if ($type == 'email')
			return '【Yeahmonitor】【'.$data['notifyType'].'】'.$data['app'].'-'.$data['service'];
		
		if ($type == 'sms')
			return '['.$data['notifyType'].']['.strip_tags($data['message']).']';
	}
	
	public function getBody($data=array()) {
		
		return <<<EOF
		<!DOCTYPE html>
		<html>
			<head>
			    <title>Yeahmonitor</title>
			    <meta charset=utf-8>
			    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
			    <style type="text/css">
			    	html {
			    		font-family:"Helvetica Neue",Helvetica,"Trebuchet MS",Arial,sans-serif;
			    	}
			    	body {
			    		padding:0 0;
			    	}
			    	i,ul,ol,li,p,div,span {
			    		font-style: normal;
			    		text-decoration: none;
			    		list-style: none;
			    		padding: 0;
			    		margin: 0;
			    	}
			    	table.gridtable {
						font-family: verdana,arial,sans-serif;
						font-size:11px;
						color:#333333;
						border-width: 1px;
						border-color: #666666;
						border-collapse: collapse;
					}
					table.gridtable th {
						border-width: 1px;
						padding: 8px;
						border-style: solid;
						border-color: #666666;
						background-color: #dedede;
					}
					table.gridtable td {
						border-width: 1px;
						padding: 8px;
						border-style: solid;
						border-color: #666666;
						background-color: #ffffff;
					}
					.name {
		
						text-align: right;
					}
			    </style>
			</head>
			<body>
					
					<table class="gridtable">
						<tr>
							<td class='name'>Time:</td>
							<td>{$data['time']}</td>
						</tr>
						<tr>
							<td class='name'>HostIP:</td>
							<td>{$data['ip']}</td>
						</tr>
						<tr>
							<td class='name'>APP:</td>
							<td>{$data['app']}</td>
						</tr>
						<tr>
							<td class='name'>Service:</td>
							<td>{$data['service']}</td>
						</tr>
						<tr>
							<td class='name'>Message:</td>
							<td>{$data['message']}</td>
						</tr>
						<tr>
							<td class='name'>LogID:</td>
							<td>{$data['logid']}</td>
						</tr>
						<tr>
							<td class='name'>Log:</td>
							<td>{$data['log']}</td>
						</tr>
					</table>
		
			</body>
		</html>
EOF;
	}	
}