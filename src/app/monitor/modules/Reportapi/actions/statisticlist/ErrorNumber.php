<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang<Fransis.shang@yeahmobi.com>
 * Date: 14-7-22
 * Time: 上午11:40
 */
class Action_ErrorNumber extends Controller_Application{

    public function run(){
        $model     = new Model_Statistic_Error();
        $to         = $this->getRequest()->getQuery('to');
        $page       = $this->getRequest()->getQuery('page', 1);

        $to = empty($to) || !isset($to) ? strtotime(date('Y-m-d')) : strtotime($to);
        $from = $to + 86400;
        $resultList = $model->get($to, self::PAGE_NUM, $page, $from);
//        print_r($resultList);exit;
        $output = array();

        if ($resultList['count']) {

            $output['series']   = $resultList['data'];
            $output['page_num'] = ceil($resultList['count'] / self::PAGE_NUM);
            $output['per_num']  = self::PAGE_NUM;
            $output['total']    = $resultList['count'];
            $output['result']   = 1;
        } else {
            $output['result'] = 0;
        }

        if ($resultList['count']) {
            static::output(1, 'ErrorNumber', $output);
        } else {
            static::output(0,'no data', array());
        }
    }
}