<?php
/*
 * 参数校验及常用功能函数
 * @author 常博<prince.chang@yeahmobi.com>
 * @version 1.0.0
 * @package ym
 * @category script
 * @copyright 2014-2015 Yeahmobi@inc
 */
 class Ym_CommonTool {
 	 /**
     *检查昵称是否符合只包含字母，汉字，以及数字的要求
     * @param <string> $nickName
     * @return <bool>true or false
     */
	public static  function checkNickName($nickName) 
	{
        $status = true;
        if($nickName)
        {
            $status = preg_match('/[\x{4e00}-\x{9fa5}0-9_a-zA-Z]/u', $nickName);
        }
		return $status;
	}
	
	/**
	 * 检查是否为正确的手机号
	 * @param <string> $phone
     * @return <bool>true or false
     */
	public static function check_phone($phone)
	{
		 if (preg_match("/^1[3,5,8][0-9]{1}[0-9]{8}$/", $phone))
		 {    
		    return true;    
		 }
		 else
		 {    
		    return false;
		 }   
	}
	
	/**
	 * 检查是否为正确的邮箱地址
	 * @param <string> $email
	 * @return <bool>true or false
	 */
	 public static function is_email($email)
	 {
		if(preg_match("/^([_.0-9a-z-]+)@([0-9a-z][0-9a-z-]+[\.])+([a-z]{2,4})$/i", $email))
		{
			return true;
		} 
		else
		{
			return false;
		}
	 }
	 
	 /**
	 * 检查URL地址合法性
	 * @param <string> $url
	 * @return <bool>true or false
	 */
	public static function is_url($url)
	{
		$pattern = '/(^(https:\/\/)([0-9a-zA-z\-\._]{0,25})\.([0-9a-zA-z\-_\.\/]{0,}))|(^(http:\/\/)([0-9a-zA-z\-\._]{0,25})\.([0-9a-zA-z\-_\.\/]{0,}))/i';
		if ( preg_match($pattern, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 获取用户ip
	 * @param 
	 * @return <string> $realip
	 */
	public static function get_ip()
	{
		if (isset($_SERVER))
		{
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			{
				$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}
			else if (isset($_SERVER["HTTP_CLIENT_IP"]))
			{
				$realip = $_SERVER["HTTP_CLIENT_IP"];
			}
			else
			{
				$realip = $_SERVER["REMOTE_ADDR"];
			}
		}
		else
		{
			if (getenv("HTTP_X_FORWARDED_FOR"))
			{
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			}
			else if (getenv("HTTP_CLIENT_IP"))
			{
				$realip = getenv("HTTP_CLIENT_IP");
			}
			else
			{
				$realip = getenv("REMOTE_ADDR");
			}
		}
		return $realip;
	}
	
	/**
	 * 密码字符检查，密码由大小写英文字母、数字、下划线、- 组成，写长度有规定
	 * @param <string> $password
	 * @return <bool>true or false
	 */
	public static function isPassword($password, $minlen=6, $maxlen=6)
	{
		if(preg_match("/^([0-9a-zA-Z_-])*$/i", $minlen=0, $maxlen=0))
		{
	        if ($minlen && (strlen($password) < $minlen))
	        {
	        	return false;
	        }
	        if ($maxlen && (strlen($password) > $maxlen))
	        {
	        	return false;
	        }
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 检查字符串长度的有效性
	 * @param <string> $info 字符串
	 * @param <int> $min 最小长度
	 * @param <int> $max 最大长度
	 * @return <bool> true or false
	 */
	 public static function isSpecifiedLen($info, $min = 0, $max = 0) {
        if (0 === $min && 0 < $max) { 
            if ($max > strlen($info))
            {
            	return true;
            } 
        }

        if (0 < $min && 0 < $max) {
            $len = strlen($info);
            if ($len > $min && $len < $max)
            {
            	return true;
            } 
        }

        if (0 < $min && 0 === $max) {
            if ($min < strlen($info))
            {
            	return true;
            } 
        }
        return false;
    }
	 
	/**
	 * 产生一个随机数
	 * @param <int> $length 随机数长度
	 * @return <string> $random
	 */
	public static function random($length)
	{
		$random = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		mt_srand((double)microtime() * 1000000);
		for($i = 0; $i < $length; $i++)
		{
			$random .= $chars[mt_rand(0, $max)];
		}
		return $random;
	}
	
	/**
	 * curl  接口
	 * @param <string> $url 请求url
	 * @param <array> $data 请求参数
	 * @param <string> $httpType 请求方式， get or post
	 * $return success: <string> $response, 接口结果 , false : <bool>false
	 */
	public static function curl($url, $data, $httpType='GET' )
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($httpType == 'POST')
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $postString = '';
            if(!empty($data))
            {
            	foreach ($data as $key => $value)
	            {
	                $postString .= $key . '=' . $value . '&';
	            }
	            $postString = substr($postString, 0, -1);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        }elseif ($httpType == 'GET')
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $getString = '';
            if(!empty($data))
            {
            	foreach ($data as $key => $value)
	            {
	                $value =urlencode($value);
	                $getString .= $key . '=' . $value . '&';
	            }
	            $getString = substr($getString, 0, -1);
	            $getString = '?'.$getString;
            }
            curl_setopt($ch, CURLOPT_URL, $url.$getString);
            echo $url.$getString;
        }

        $response = curl_exec($ch);
		
		//log记录每次请求的url ，参数，以及返回值
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code == 200)
        {
           return $response;
        } else
        {
            return false;
        }
    } 
    
    /**
     * 文件上传
     * @param <string> $file 文件流
     * @param <string> $destination 文件存放目录
     * @param <string> $savename 存放文件名称
     * @param <string> $ext 存放文件名后缀
     * 
     * $return <string> 文件存储路径
     * */
	public static function uploadFile($file, $destination, $savename, $ext)
    {
    	
    }
    
    /**
     * 写日志
     * @param <string> $info log内容
     * @param <string> $dir 日志存放路径
     * @param <string> $name 日志文件名
     * @param <string> $ext 文件名后缀
     * */
	public static function writeLog($this, $info, $dir, $name, $ext) {
		
	}
	
	public static function output($obj, $res, $type = 'jsonp', $status_code = 200) {
		if(!empty($res)) {
			if($type == 'jsonp') {
				$jsoncallback = $obj->getRequest()->getQuery('callback', '');
				if($jsoncallback) {
					echo $jsoncallback . "('" . json_encode($res) . "')";
					exit;
				}
				else {
					echo json_encode($res);
					exit;
				}
			}
			else {
				if(!empty($_SERVER['HTTP_ORIGIN'])) {
					header("Access-Control-Allow-Credentials: true");
					header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
					header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
				}
				//http_response_code($status_code);
				echo json_encode($res);
                unset($res);
				exit;
			}
		}
		exit;
	}

     public function myoutput($flag=TRUE, $msg='', $data=array()) {
    	
    	if ($flag) {
    		$out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
    	} else {
    		$out = array('flag' => 'error', 'msg' => $msg, 'data' => $data);
    		Ym_Logger::error($msg);
    	}
    	
    	self::output($this, $out, 'json');
    }
    
	/**
	 * 
	 * yafclient exec Add by zhangy
	 * @param str $url
	 * @param bool $asJob
	 */
	public function phpCli($url='', $asJob=TRUE, $return=TRUE) {
		
		$job = "";
		$out = ">/dev/null 2>&1";
		$phpcmd = "/bin/php";
		$yafClient = Ym_Config::getAppItem("application:application.client");
		$requestUri = "request_uri=".$url;
		
		if ($asJob) $job = "&";
		if ($return) $out = "";
	    $cmd = trim($phpcmd." ".$yafClient." '".$requestUri."' ".$out." ".$job);

	    Ym_Logger::info($cmd);
	    return system($cmd);
	} 
}