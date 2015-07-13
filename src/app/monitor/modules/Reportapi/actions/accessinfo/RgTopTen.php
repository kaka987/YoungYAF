<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang<Fransis.shang@yeahmobi.com>
 * Date: 14-7-18
 * Time: 下午4:02
 */

class Action_RgTopTen extends Controller_Application
{
    public function run(){
        $model       = new Model_ReportApi_Accesslog();
        $hosts = $model->getBusiness();
        $day            = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
//        echo $this->getRequest()->getQuery('from', date('Y-m-d'));exit;

        $where          = "WHERE `time`= {$day} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";
        $res            = $model->mapStatisticsTopTen($where);
//        print_r($res);exit;
        $result = array();
        foreach ($res as $val) {
            $result['series'][] = array('code' => $val['country_code'],
                'name' => $val['country_name'],
                'num'  => $val['num'],);
        }

        if(empty($res)){
            static::output(0,'no data',array());
        }else{
            static::output(1, 'RgTopTen',$result);
        }
        //测试数据
//        $result['series'] = array(
//            array('code' =>425,
//            'name' => 'china',
//            'num'  => 123),
//            array('code' =>435,
//                'name' => 'USA',
//                'num'  => 45),
//        );
//        static::output(1, 'RgTopTen',$result);

//        return $result;
    }
}