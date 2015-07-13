<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang
 * Date: 14-7-22
 * Time: 上午11:25
 */

class Action_RequestDataSize extends Controller_Application{

    public function run(){
        $model     = new Model_Statistic_DataSize();

        $to         = $this->getRequest()->getQuery('to');
        $page       = $this->getRequest()->getQuery('page', 1);

        $to = empty($to) || !isset($to) ? strtotime(date('Y-m-d')) : strtotime($to);

        $resultList = $model->get($to, self::PAGE_NUM, $page);
//        print_r($resultList);exit;
        $output = array();

        if ($resultList['count']) {

            foreach ($resultList['data'] AS $key => $value) {
                $resultList['data'][$key]['total_size'] = self::convertSize($value['total_size']);
            }
            $output['series']     = $resultList['data'];
            $output['page_num'] = ceil($resultList['count'] / self::PAGE_NUM);
            $output['per_num']  = self::PAGE_NUM;
            $output['result']   = 1;
            $output['total'] = $resultList['count'];
        } else {
            $output['result'] = 0;
        }

        if($resultList['count']){
            static::output(1, 'RequestDataSize', $output);
        }else{
            static::output(0,'no data', array());
        }
    }
}
