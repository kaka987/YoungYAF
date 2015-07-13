<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang
 * Date: 14-7-22
 * Time: 下午2:03
 */
class Action_ResponseTime extends Controller_Application{
    public function run(){

        $model     = new Model_Statistic_RequestTime();

        $to         = $this->getRequest()->getQuery('to');
        $page       = $this->getRequest()->getQuery('page', 1);

        $to = empty($to) || !isset($to) ? strtotime(date('Y-m-d')) : strtotime($to);
        $from = $to + 86400;
//        echo $to;exit;
        $resultList = $model->get($to, self::PAGE_NUM, $page, $from);
//        print_r($resultList);
//        exit;
        $output = array();
//        echo $resultList['count'],'--', self::PAGE_NUM;
//        exit;
        if ($resultList['count']) {

            $output['series']     = $resultList['data'];
            $output['page_num'] = ceil($resultList['count'] / self::PAGE_NUM);
            $output['per_num']  = self::PAGE_NUM;
            $output['result']   = 1;
            $output['total']    = $resultList['count'];
        } else {
            $output['result'] = 0;
        }

        if($resultList['count']){
            static::output(1, 'ResponseTime', $output);
        }else{
            static::output(0,'no data', array());
        }
    }
}