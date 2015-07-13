<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang
 * Date: 14-7-18
 * Time: 下午4:43
 */

class Action_RtcTopTen extends Controller_Application
{
    public function run(){
        $model       = new Model_ReportApi_Accesslog();
        $hosts = $model->getBusiness();
        $day            = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $where          = " and `time`={$day} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";
        $res            = $model->rtcTopTen($where);
//        print_r($res);exit;
//        $result['name'] = 'rtcTopTen';
        $result = array();
        foreach ($res as $value) {
            $result['series'][] = array('ip'     => long2ip($value['ip']),
                'name'  => ucwords(strtolower($model->ip2location($value['ip']))),
                'second'=> $value['total_request_time'],
                'path' 	=> $value['path'],
            	'host' 	=> $value['host']
            );
        }
//        print_r($result);exit;
        if(empty($res)){
           static::output(0,'no data',array());
        }else{
            static::output(1,'RtcTopTen',$result);
        }

//      测试数据
//        $result['series'] = array(
//            array('ip'     => '172.30.10.222',
//            'name'   => 'china',
//            'second' => '1123',
//            'path' => '/root'
//            ),
//            array('ip'     => '192.40.30.222',
//                'name'   => 'USA',
//                'second' => '1589',
//                'path' => '/usr/local'
//            ),
//        );
//        static::output(1,'RtcTopTen',$result);
    }
}