<?php
class Action_Edit extends Controller_Application {

    protected $layout = 'main';

    public function run()
    {
    	
    	$alarmConfig = new Model_Alarm_Config();
    	
    	$configData = array();
    	$id = $this->getRequest()->getQuery('id');
    	if ($id) $configData = $alarmConfig->selectdata($id);
    	$configData['log'] = $alarmConfig->getlogConfig();
    	
    	$configData['api'] = Ym_Config::getAppItem("actions:monitorscript");
    	
    	$configData[0]['ouser'] = $alarmConfig->getMonitorUser();
    	$configData[0]['ogroup'] = $alarmConfig->getMonitorGroup();
    	
    	$this->getView()->assign('L', $configData?$configData:array());
    }
}