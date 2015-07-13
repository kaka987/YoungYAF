<?php

class Controller_Email extends Yaf_Controller_Abstract
{
    public function init() {
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function dailyAction() {
        $this->initView();

        $hostStr   = $this->getRequest()->getQuery('host');
        $from      = strtotime($this->getRequest()->getQuery('from', date('Y-m-d', time() - 86400)));
        $to        = strtotime($this->getRequest()->getQuery('to'));
        $trendFrom = $from;

        if (empty($to)) {
            $to        = $from + 86400;
            $trendFrom = $from - 86400;
        }

        $systemHostService       = new Service_System_Host();
        $analyticsAccessService  = new Service_Analytics_Access();

        $hosts = $systemHostService->getHostId($hostStr);

        $errorList  = $analyticsAccessService->getList(Service_Analytics_Access::LIST_ACCESS, $hosts, $from, $to, 1, array("status" => "500,502,504,505"));
        $sizeList   = $analyticsAccessService->getList(Service_Analytics_Access::LIST_SIZE, $hosts, $from, $to, 1);
        $timesList  = $analyticsAccessService->getList(Service_Analytics_Access::LIST_TIMES, $hosts, $from, $to, 1);

        $pageVisit  = $analyticsAccessService->getIndex($hosts,'pv', $from, $to);
        $errorVisit = $analyticsAccessService->getIndex($hosts,'error', $from, $to);

        $minuteMax  = $analyticsAccessService->getRequestMinute('max', $hosts, $from, $to);
        $minuteMin  = $analyticsAccessService->getRequestMinute('min', $hosts, $from, $to);

        $data = array(
            "page_visit"   => $pageVisit,
            "error_visit"  => $errorVisit,
            "minute_max"   => $minuteMax,
            "minute_min"   => $minuteMin,
            "error_list"   => $errorList,
            "size_list"    => $sizeList,
            "times_list"   => $timesList
        );

        $this->getView()->assign('host', $hostStr);
        $this->getView()->assign('data', $data);
        $this->getView()->assign('trendFrom', $trendFrom);
        $this->getView()->assign('to', $to);

        $email = $this->render('daily');

        echo $email;

//        Sys_Message::send('mail', 'chiaki.sun@yeahmobi.com', '监控日报', $email, '172.30.10.111');
    }
} 