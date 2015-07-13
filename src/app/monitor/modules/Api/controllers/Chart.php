<?php
class Controller_Chart extends Yaf_Controller_Abstract
{
    public function configAction() {
        $type     = $this->getRequest()->getQuery('type');
        $hosts    = trim($this->getRequest()->getQuery('host'));
        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to       = strtotime($this->getRequest()->getQuery('to'));

        if (empty($to)) {
            $to   = $from + 86400;
            $from = $from - 86400;
        }

        $output   = null;

        $serviceChartConfig = new Service_Chart_Config();
        $systemHostService  = new Service_System_Host();

        $hosts = $systemHostService->getHostId($hosts);

        $option = array('from' => $from, 'to' => $to);

        $output = $serviceChartConfig->get($type, $hosts, $option);

        Sys_Common::output(true, '', $output);
    }

    public function screenAction() {
        $type    = $this->getRequest()->getQuery('type');
        $hosts   = trim($this->getRequest()->getQuery('host'));
        $from    = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to      = strtotime($this->getRequest()->getQuery('to'));

        if (empty($to)) {
            $to   = $from + 86400;
            $from = $from - 86400;
        }

        $output  = null;

        $serviceChartScreen = new Service_Chart_Screen();
        $serviceChartConfig = new Service_Chart_Config();
        $systemHostService  = new Service_System_Host();

        $hosts = $systemHostService->getHostId($hosts);

        $option = array('from' => $from, 'to' => $to);

        $config = json_encode($serviceChartConfig->get($type, $hosts, $option));
        $output = $serviceChartScreen->get($config);

        Sys_Tools::response(base64_decode($output), "image/png");
    }

} 