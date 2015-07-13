<?php

/**
 * 
 * 监控进程启动时，根据监控配置，更新监控服务的
 * @author young<young@yeahmobi.com>
 *
 */
class Action_Test extends Yaf_Action_Abstract
{
    public function execute()
    {

    	$p = $this->getRequest()->getParams();
    	if (empty($p)) $p = $this->getRequest()->getQuery();
    	$type 	= isset($p['type']) ? $p['type'] : 'email';
    	$to 	= isset($p['to']) ? $p['to'] : '';
    	$title	= isset($p['title']) ? $p['title'] : '';
    	$body	= isset($p['body']) ? $p['body'] : '';
    	$ip	    = isset($p['ip']) ? $p['ip'] : '127.0.0.1';
    	$app	= isset($p['app']) ? $p['app'] : 'monitor';
    	
    	if ($type=='email') $this->sendByEmail($to, $title, $body, $ip, $app);
    	if ($type=='sms') $this->sendBySms($to, $title, $body, $ip, $app);
    	
        return FALSE;
    }
    
	public function sendByEmail($emailList, $title, $body, $ip, $app) {

		Ym_Logger::info('test---Sendmail#'.$emailList);
		$r = Sys_Message::send('mail', $emailList, $title, $body, $ip, $app);
		
		var_dump(json_decode(urldecode($r),true));
	}
	
	public function sendBySms($to, $title, $body, $ip, $app) {
	
		echo 'test---Sendsms#'.$to;
		$r = Sys_Message::send('sms', $to, $title, $body, $ip, $app);
		
		var_dump(json_decode(urldecode($r),true));
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