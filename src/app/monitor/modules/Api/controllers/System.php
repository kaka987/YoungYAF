<?php
class Controller_System extends Yaf_Controller_Abstract
{
    public $regionService;

    public function init() {
        $this->regionService = new Service_System_Region();
    }

    public function regionAction() {
        $domain = $this->getRequest()->getQuery('domain');

        $region = array("domain" => $domain);

        $list = $this->regionService->getAllRegion();
        $one  = $this->regionService->getRegion($region);

        $output = array(
            'list' => $list,
            'self' => $one
        );

        Sys_Common::output(true, '', $output);
    }

}