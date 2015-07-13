<?php
/***
 * Class Action_ErrorTopten
 *
 * @author      Fransis.shang<fransis.shang@yeahmobi.com>
 * @package     /modules/dataconvert/action/convert
 * @since       Version 1.0.1 @2014/7/16
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Action_ErrorTopten extends Controller_Application
{
    protected $isNeedLogin = User_Session::LOGIN_WITHOUT;
    public function run() {
        $model = new Model_ReportApi_Accesslog();
        $hosts = $model->getBusiness();
        $from = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to = $from + 86400;
//        echo $from;
//        exit;
        $where          = " AND `time`< {$to} AND time > {$from} AND status >= 400";
//		$where          = " AND status != 200";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";
//        $result['name'] = 'errorTopTen';
        $result['series'] = $model->errorTopTen($where);
//        print_r($model->errorTopTen($where)); exit;
        if($result['series']){
            static::output(1,'ErrorTopTen',$result);
        }else{
            static::output(0,'no data',array());
        }

    }
}
