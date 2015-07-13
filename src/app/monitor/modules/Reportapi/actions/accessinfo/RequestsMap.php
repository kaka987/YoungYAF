<?php

class Action_RequestsMap extends Controller_Application
{
    public function run()
    {
        $model = new Model_ReportApi_Accesslog();

        $hosts = $model->getBusiness();

        $day = strtotime($this->getRequest()->getQuery('from', date('Y-m-d 00:00:00')));

        $where = "WHERE `time`= {$day}";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $res = $model->mapStatistics($where);

        $country = $model->globalCountry();

        foreach ($country AS $v) {
            $result['series'][$v['code']] = array('code'  => $v['code'],
                'value' => 0,
                'name'  => $v['name']
            );
        }

        foreach ($res as $val) {
            $result['series'][$val['country_code']] = array('code'  => $val['country_code'],
                'value' => $val['num'],
                'name'  => $val['country_name'],
            );
        }

        static::output(1, 'RequestsMap', $result);
    }
}