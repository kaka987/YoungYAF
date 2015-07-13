<?php
class Action_ErrorTopTen extends Yaf_Action_Abstract
{
    public function execute() {
        $hosts    = trim($this->getRequest()->getQuery('host'));
        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to       = strtotime($this->getRequest()->getQuery('to'));
        $output   = array();

        if (empty($to)) {
            $to   = $from + 86400;
        }

        $analyticsAccessService = new Service_Analytics_Access();
        $systemHostService      = new Service_System_Host();

        $hosts  = $systemHostService->getHostId($hosts);
        $output['series'] = $analyticsAccessService->getErrorTopTen($hosts, $from, $to);

        Sys_Common::output(true, '', $output);
    }
} 