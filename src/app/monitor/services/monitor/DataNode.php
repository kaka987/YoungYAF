<?php
class Service_Monitor_DataNode
{
    public function get() {
        $returns = array();

        $monitorDataNodeModel = new Model_Monitor_DataNode();
        $returns = $monitorDataNodeModel->get();

        return $returns;
    }
} 