<?php
/**
 * 访问流量列表
 *
 * @author      Fransis.shang<Fransis.shang@yeahmobi.com>
 * @package     actions/statisticlist
 * @since       Version 1.0 @2014-07-04
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Action_RequestDataSize extends Controller_Application {

    protected $layout = 'main';
    protected $isNeedLogin = User_Session::LOGIN_MUST;

    //只是负责展示的是requestdatasize的页面，展示的页面中使用AJAX来进行请求api接口
    public function run()
    {

    }

}