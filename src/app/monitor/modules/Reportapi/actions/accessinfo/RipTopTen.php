<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang
 * Date: 14-7-18
 * Time: 下午4:24
 */

class Action_RipTopTen extends Controller_Application
{
    public function run(){
        $model       = new Model_ReportApi_Accesslog();
        $hosts = $model->getBusiness();
        $day            = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $where          = " and `time`={$day} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";
        $ret            = $model->getRipTopTen($where);
//        $result['name'] = 'ripTopTen';
//        print_r($ret);
        $result = array();
        foreach ($ret as $value) {
            $result['series'][] = array('ip'   => long2ip($value['ip']),
                'name' => ucwords(strtolower($model->ip2location($value['ip']))),
                'num'  => $value['num'],
                'path' => $value['path'],
            	'host' => $value['host']
            );
        }
//        print_r($result);
        if(empty($ret)){
            static::output(0,'no data',array());
        }else{
            static::output(1,'RipTopTen',$result);
        }
        //测试数据
//        $result['series'] = array(
//            array('ip'   => '172.30.10.11',
//            'name' => 'china',
//            'num'  => 33012,
//            'path' => '/'
//            ),
//            array('ip'   => '192.20.10.103',
//                'name' => 'USA',
//                'num'  => 12346,
//                'path' => '/root'
//            ),
//        );
////        print_r($result);exit;
//        static::output(1,'RipTopTen',$result);

    }
}