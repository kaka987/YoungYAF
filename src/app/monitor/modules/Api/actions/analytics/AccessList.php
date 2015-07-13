<?php
class Action_AccessList extends Yaf_Action_Abstract
{

    public function execute() {
        $type      = $this->getRequest()->getQuery('type');
        $status    = $this->getRequest()->getQuery('status');
        $page      = $this->getRequest()->getQuery('page', 1);
        $host      = $this->getRequest()->getQuery('host');
        $path      = $this->getRequest()->getQuery('path');
        $from      = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to        = strtotime($this->getRequest()->getQuery('to'));
        $to        = empty($to) ? $from + 86400 : $to;
        $output    = array();
        $condition = array();

        if (empty($type)) {
            return false;
        }
        if (!empty($status)) {
            $condition['status'] = $status;
        }

        $systemHostService      = new Service_System_Host();
        $analyticsAccessService = new Service_Analytics_Access();

        $hostPathID = array();
        foreach (explode(',',$host) as $h) {
            $hosts = $systemHostService->getHostPathId($h, $path);
            if ($hosts) {
                $hostPathID[] = array($hosts[0]['host_id'],$hosts[0]['path_id']);
            }
        }

        $output = $analyticsAccessService->getList($type, $hostPathID, $path, $from, $to, $page, $condition);

        Sys_Common::output(true, '', $output);
    }
} 
