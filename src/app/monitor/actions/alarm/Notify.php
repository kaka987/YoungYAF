<?php
class Action_Notify extends Controller_Application {

    protected $layout = 'main';

    public function run() {
    	
    	$id = $this->getRequest()->getQuery();
    	
    	$this->alarmModel = $this->loadModel();
    	$alarmList = $this->alarmModel->selectData();
    	$this->getView()->assign('alarmList', $alarmList);
    }

    private function loadModel() {
    	return new Model_Alarm_Config();
    }
}