<?php
class Action_DataNode extends Yaf_Action_Abstract
{
    function execute() {

        $output = array();

        $hosts    = trim($this->getRequest()->getQuery('host'));

        $monitorDataNodeService = new Model_Alarm_DataNode();

        $output['series'] = $monitorDataNodeService->getNodeData($hosts);

        Sys_Common::output(true, '', $output);
    }
} 