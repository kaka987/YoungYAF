<?php
class Action_Alarm extends Yaf_Action_Abstract
{
    function execute() {
        $type = $this->getRequest()->getQuery("type");
        $type = explode(",", $type);

        $output = array();
        $series = array();

        $monitorAlarmService = new Service_Monitor_Alarm();

        foreach ($type AS $value) {
            $series[$value] = $monitorAlarmService->getCount($value);
        }

        $output['series'] = $series;

        Sys_Common::output(true, '', $output);
    }
} 