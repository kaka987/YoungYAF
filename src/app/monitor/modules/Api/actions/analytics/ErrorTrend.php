<?php
class Action_ErrorTrend extends Yaf_Action_Abstract
{
    public function execute() {
        $hosts    = trim($this->getRequest()->getQuery('host'));
        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to       = strtotime($this->getRequest()->getQuery('to'));
        $to       = empty($to) ? $to : $from + 86400;
        $output   = array();

        $analyticsAccessService = new Service_Analytics_Access();
        //$systemHostService      = new Service_System_Host();
        //$logPersistentService   = new Service_Log_Persistent();

       // $recordTime = $logPersistentService->getRecordTime('accesslog');

        /*if (empty($to) || $to - $from = 86400) {
            $to = $recordTime - 60;
        }*/

        $output['series'] = $analyticsAccessService->getErrorTrend($hosts, $from, $to);
        /*array(502=>array(
            array("time"=>1418601900,"num"=>10,"top"=>array(array('credit.gamelala.com','/hascredit/cache/getAppCredit',1))),
            array("time"=>1418605900,"num"=>13,"top"=>array(array('credit.gamelala.com','/hascredit/cache/getAppCredit',1))),
        ));*///$analyticsAccessService->getErrorTrend($hosts, $from, $to);

        Sys_Common::output(true, '', $output);
    }
} 
