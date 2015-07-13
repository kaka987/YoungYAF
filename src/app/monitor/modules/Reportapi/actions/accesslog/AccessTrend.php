<?php
/***
 * Class Action_Access_trend
 *
 * @author      Fransis.shang<fransis.shang@yeahmobi.com>
 * @package     /modules/dataconvert/action/convert
 * @since       Version 1.0.1 @2014/7/16
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Action_AccessTrend extends Controller_Application
{
    public function run() {
        $model = new Model_ReportApi_Accesslog();

        $hosts     = $model->getBusiness();
        $todayOnly = $this->getRequest()->getQuery("todayOnly", 0);
        $from      = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $current   = strtotime(date('Y-m-d'));
        $record    = $model->getAccessRecord();

        if ( $from == $current ) {
            $to = strtotime(date('Y-m-d H:i', $record));
        } else {
            $to = $from + 86400;
        }

        $yesterday = $from - 86400;

        $where = "WHERE `time`> {$from} AND `time` <= {$to} ";
        $where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

        $output      = array();
        $result      = $model->getAccessTrend($where);
        $today_array = array();

        foreach ($result as $value) {
            $today_array[] = array('time' => $value['time'], 'num' => $value['num']);
        }

        if ( $todayOnly ) {
            $output['series'] = array(
                'toDay'   => $today_array
            );
        } else {
            $yesterday_array  = array();

            $yestoday_where   = "WHERE `time` > {$yesterday} AND `time`<= {$from}";
            $yestoday_where .= empty($hosts) ? '' : " AND `host_id` in ({$hosts})";

            $yesterday_result = $model->getAccessTrend($yestoday_where);

            foreach ($yesterday_result as $value) {
                //此处+86400为前端方便显示两天对比的值
                $yesterday_array[] = array('time' => $value['time'] + 86400, 'num' => $value['num']);
            }
            $output['series'] = array(
                'toDay'   => $today_array,
                'yestDay' => $yesterday_array,
            );
        }
        //print_r($output);
        //return $output;
        //此处做判断数据是否有
        static::output(1,'AccessTrend',$output);
    }
}
