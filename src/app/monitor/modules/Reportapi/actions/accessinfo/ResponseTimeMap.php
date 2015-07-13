<?php

class Action_ResponseTimeMap extends Controller_Application
{
    public function run()
    {
        $model = new Model_ReportApi_Accesslog();

        $hosts = $model->getBusiness();

        $day = strtotime($this->getRequest()->getQuery('from', date('Y-m-d 00:00:00')));

        $where = "WHERE `time`= {$day}";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $res = $model->mapRequestTime($where);

        $country = $model->globalCountry();

        foreach ($country AS $v) {
            $result['series'][$v['code']] = array('code'  => $v['code'],
                'avg_request_time' => 0,
                'max_request_time' => 0,
                'name'             => $v['name']
            );
        }

        foreach ($res as $val) {
            $result['series'][$val['country_code']] = array('code'  => $val['country_code'],
                'avg_request_time' => number_format($val['total_request_time'] / $val['num'], 3),
                'max_request_time' => $val['max_request_time'],
                'name'             => $val['country_name'],
            );
        }

        static::output(1, 'ResponseTimeMap', $result);
    }
}