<?php
/**
 * Action扩展类
 *
 * 实现了用户登录权限控制，视图布局，视图是否输出
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     controllers
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
abstract class Controller_Application extends Yaf_Action_Abstract
{
    protected $layout;
    protected $isLogin;
    protected $isNeedLogin = User_Session::LOGIN_WITHOUT;
    protected $loginUser;
    protected $display;

    const PAGE_NUM = 30;

    // 所有Action必须实现run方法，由Controller调用
    public abstract function run();

    public function execute()
    {
        /*$this->isLogin = User_Session::detectLogin();

        if($this->isNeedLogin == User_Session::LOGIN_MUST) {

            $this->detectLogin();
        }

        $this->loginUser = User_Session::getLoginCookie();*/

        $this->getView()->setLayout($this->layout);

        $this->run();

        if($this->display == 'none') {
            return false;
        }
    }

    public function detectLogin() {

        if(!$this->isLogin) {

            $account = array(
                'email'    => 'anonymous@yeahmobi.com',
                'password' => '123456'
            );

            $accountModel = new Model_User_Account();

            $user = $accountModel->getUser($account);

            $accountModel->login($user);

            $this->redirect('/index');

            return false;

            // 保存登陆返回页面
            $backPage = $this->getRequest()->getServer('REQUEST_URI');
            User_Session::setBackPage($backPage);
            $this->getResponse()->setRedirect('/personal/signin');
        }
    }
    
    // if need end the request , so output
    public static function output($flag=TRUE, $msg='', $data=array()) {
    	
    	// output json {"flag":"error","msg":"","data":[]}
    	if ($flag) {
            $out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
        } else {
    		$out = array('flag' => 'error', 'msg' => $msg, 'data' => $data);
    		Ym_Logger::error($msg);
    	}
    	
    	Ym_CommonTool::output(NULL, $out, 'json');
    	exit;
    }

    //using print_r ,formating data ,then exit
    public static  function print_info($info){
        echo '<pre>';
        print_r($info);
        echo '</pre>';
        exit;
    }
    
	// if need end the request , so output
    public static function outputJsonp($flag=TRUE, $msg='', $data=array(), $obj=NULL) {
    	
    	// output json {"flag":"error","msg":"","data":[]}
    	if ($flag) $out = array('flag' => 'success', 'msg' => $msg, 'data' => $data);
    	else {
    		$out = array('flag' => 'error', 'msg' => $msg, 'data' => $data);
    		Ym_Logger::error($msg);
    	}
    	
    	Ym_CommonTool::output($obj, $out, 'jsonp');
    	exit;
    }

    // convert datasize
    public static function convertSize($size)
    {
        $return = '';
        if ($size > pow(1024, 3)) {
            $return = number_format($size / pow(1024, 3), 3) . " GB";
        } else if ($size > pow(1024, 2)) {
            $return = number_format($size / pow(1024, 2), 3) . " MB";
        } else if ($size > 1024) {
            $return = number_format($size / 1024, 3) . " KB";
        } else {
            $return = number_format($size, 0) . " 字节";
        }

        return $return;
    }
}
