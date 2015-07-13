<?php
class Action_Save extends Controller_Application {

	//protected $layout = 'main';
	public $configData = array();
	
	public $alarmModel = NULL;
	
    public function run() {
    	
    	$this->alarmModel = $this->loadModel();
    	
    	$this->configData = $this->getPostData($this->checkPostData());
    	$result = $this->{$this->getActionType()."Data"}();
    	
    	if ($result) {
    		static::output(1,'Query is OK',$result);
    	} else {
    		static::output(0,'Query is error!',array());
    	}
    	
    	$this->display = 'none';
    }
    
    private function loadModel() {
    	
    	return new Model_Alarm_Config();
    }
    
    private function checkPostData() {
    	
    	$postData = $this->getRequest()->getPost();
    	
    	if (isset($postData['foo']) AND $postData['foo']=='bar') {
    		return $postData;	
    	}
    	
    	if (!isset($postData['monitor_service']) OR !$postData['monitor_service']) {
    		static::output(FALSE,'Param is error!');
    	}
    	
    	return $postData;
    }
    
    private function getPostData($configData) {
    	 
    	/*if (isset($configData['notify_contact']) AND $configData['notify_contact']) {
    		$configData['notify_contact'] = implode(',', $configData['notify_contact']);
    	}*/
    	
    	$arr = array('notify_method','notify_type','notify_userid','notify_groupid');
    	
    	foreach($arr as $k) {
    		
	    	if (isset($configData[$k]) AND $configData[$k]) {
	    		$configData[$k] = implode(',', $configData[$k]);
	    	}
    	}
    	
    	/*if (isset($configData['notify_method']) AND $configData['notify_method']) {
    		$configData['notify_method'] = implode(',', $configData['notify_method']);
    	}
    	 
    	if (isset($configData['notify_type']) AND $configData['notify_type']) {
    		
    		$configData['notify_type'] = implode(',', $configData['notify_type']);
    	}
    	
    	if (isset($configData['notify_userid']) AND $configData['notify_userid']) {
    		
    		$configData['notify_userid'] = implode(',', $configData['notify_userid']);
    	}
    	
    	if (isset($configData['notify_groupid']) AND $configData['notify_groupid']) {
    		
    		$configData['notify_groupid'] = implode(',', $configData['notify_groupid']);
    	}*/
    	
    	return $configData;
    }
    
    private function getActionType() {
    	
    	if (isset($this->configData['monitor_id']) AND $this->configData['monitor_id']) {
    		if (isset($this->configData['foo']) AND $this->configData['foo']=='bar')
    			return 'delete';
    		return 'update';
    	}
    	return 'insert';
    }
    
    private function insertData() {
    	
    	if ($this->configData) {
    		unset($this->configData['monitor_id']);
    		return $this->alarmModel->{__FUNCTION__}($this->configData);
    	}
    	return FALSE;
    }
    
    private function updateData() {
    	if ($this->configData) {
	    	$where = 'id='.$this->configData['monitor_id'];
	    	unset($this->configData['monitor_id']);
	    	return $this->alarmModel->{__FUNCTION__}($this->configData, $where);
    	}
    	return FALSE;
    }
    
    private function deleteData() {
    	
    	return $this->alarmModel->{__FUNCTION__}($this->configData['monitor_id']);
    }
}