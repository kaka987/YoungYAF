<?php
class Action_ClickHour extends Yaf_Action_Abstract
{
    function execute() {

        $output = array();

        //$hosts    = trim($this->getRequest()->getQuery('host'));

        $monitorDataNodeService = new Model_Alarm_DataNode();

        $output['series'][] = array('name'=>'click','data'=>$monitorDataNodeService->getHourClick('click'));
        $output['series'][] = array('name'=>'conv','data'=>$monitorDataNodeService->getHourClick('conv'));

        Sys_Common::output(true, '', $output);
    }
} 