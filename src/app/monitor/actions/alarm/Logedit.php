<?php
class Action_Logedit extends Controller_Application {

    protected $layout = 'main';

    public function run()
    {
    	
    	$alarmConfig = new Model_Alarm_Log();

    	$id = $this->getRequest()->getQuery('id');
    	if ($id) {
            $configData = $alarmConfig->selectdata($id);

            $configData['0']['selectedWorkerAction'] = $alarmConfig->selectedWorkerAction($id);
        }

        $configData[0]['worker'] = $alarmConfig->getWorkers();
        $configData[0]['actions'] = $alarmConfig->getActions();

        $this->getView()->assign('L', isset($configData[0])?$configData[0]:array());
    }
}