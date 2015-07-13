<?php
class Action_Users extends Controller_Application {

    protected $layout = 'main';

    public function run() {
    	
    	$this->userTable = Sys_Database::getTable('user');
    	$this->relation_user_group = Sys_Database::getTable('relation_user_group');
    	$this->alarmModel = $this->loadModel();
    	
    	if ($this->getRequest()->getPost('o') == 'delete') {
    		$uid = $this->getRequest()->getPost('uid');
    		$this->delete($uid);
    	}
    	
    	if ($this->getRequest()->getQuery('o') == 'edit') {
    		
    		 Yaf_Dispatcher::getInstance()->disableView();
    		 $this->initView();
    		 
    		 $configData = array();
    		 $id = $this->getRequest()->getQuery('id');
    		 if ($id) {
    		 	
    		    $configData = $this->alarmModel->selectData($id,1,1,$this->userTable);
    		 	$configData[0]['group_id'] = $this->alarmModel->getUGIds($id,0);
    		 }
    		 
    		 $configData[0]['ogroup'] = $this->alarmModel->getMonitorGroup();
    		 $this->display('usersadd', array('D'=>$configData[0]));
    	}
    	
    	if ($this->getRequest()->getQuery('o') == 'add') {
    		 $this->add();
    	}
    	 
    	$page = $this->getRequest()->getQuery('p');
    	$alarmList['page'] = $page ? $page : 1;
    	$id = $this->getRequest()->getQuery('uid');
    	$uid = $id ? $id : NULL;
    	$limit = 20;
    	
    	$alarmList['list'] = $this->alarmModel->selectData($uid, ($alarmList['page']-1)*$limit, $limit, $this->userTable);
    	$alarmNum = $this->alarmModel->selectData($uid, -1, -1, $this->userTable);
    	$alarmList['num'] = $alarmNum['num'];
    	
    	$alarmList['pageCount'] = (int)($alarmList['num']/$limit) + 1;
    	$alarmList['pageView'] = $this->page($alarmList['page'], $alarmList['pageCount']);
    	
    	$this->getView()->assign('alarmList', $alarmList);
    }


    
    public function delete($uid=0) {
    	
    	if ($uid==0) Ym_CommonTool::myoutput(0,'Query is error!');
    	
    	$this->alarmModel->deleteUserGroup($uid);
    	$result =  $this->alarmModel->deleteData($uid, $this->userTable);
    	if ($result) {
    		Ym_CommonTool::myoutput(1,'Query is OK!',$result);
    	} else {
    		Ym_CommonTool::myoutput(0,'Query is error!',array());
    	}
		
    	$this->display = 'none';
    }
    
    public function add() {
    
    	$this->configData = $this->getPostData($this->checkPostData());
    	
    	if (isset($this->configData['id']) AND $this->configData['id']) {
    		$result =  $this->updateData();
    	} else {
    		$result =  $this->insertData();
    	}
    	if ($result) {
    		Ym_CommonTool::myoutput(1,'Query is OK!',$result);
    	} else {
    		Ym_CommonTool::myoutput(0,'Query is error!',array());
    	}
    	
    	$this->display = 'none';
    }
    
     private function checkPostData() {
    	
    	$postData = $this->getRequest()->getPost();
    	
    	if (isset($postData['foo']) AND $postData['foo']=='bar') {
    		return $postData;	
    	}
    	
    	if (!isset($postData['email']) OR !$postData['email']) {
    		Ym_CommonTool::myoutput(FALSE,'Param is error!');
    	}
    	
    	return $postData;
    }
    
    private function getPostData($configData) {
    	 
    	$arr = array('notify_method','notify_type','apps','hosts','services','group_id');
    	
    	foreach($arr as $k) {
    		
	    	if (isset($configData[$k]) AND $configData[$k]) {
	    		$configData[$k] = implode(',', $configData[$k]);
	    	}
    	}
    	
    	return $configData;
    }
    
    public function page($p, $pageCount) {
    
    	$page = '';
    	$active = '';
		$pageStart = 1;
        if($p>10) $pageStart = $p-9;
        $pageMax = ($pageCount<10) ? $pageCount+1 : $pageStart + 10;
        
        $page  = '<ul>';
        $page .= '<li class="previous disabled"><a href="#">&#8249;</a></li>';
        /*for($i=$pageStart;$i<$pageMax;$i++) {
        	if($i==$p) $active = 'active';
        	$page .= '<li><a class="'.$active.'" href="?p='.$i.'">'.$i.'</a></li>';
        }*/
        
        $page .= '<li class="next"><a href="#">&#8250;</a></li>';
        $page .= '</ul>';
        
        return $page;
    }
    
    private function insertData() {
    	
    	if ($this->configData) {
    		unset($this->configData['id']);
    		$uid = $this->alarmModel->{__FUNCTION__}($this->configData, $this->userTable);
    		if ($uid AND $this->configData['group_id']) {
    			$gids = explode(',', $this->configData['group_id']);
    			return $this->alarmModel->insertUserGroup($uid,$gids);
    		}
    		return $uid;
    	}
    	return FALSE;
    }
    
    private function updateData() {
        //var_dump($this->configData);exit;
    	if ($this->configData) {
	    	$where = 'id='.$this->configData['id'];
	    	$uid = $this->configData['id'];
	    	unset($this->configData['id']);
	    	$r = $this->alarmModel->{__FUNCTION__}($this->configData, $where, $this->userTable);
	    	if ($r) {
	    		if ($this->configData['group_id']) {
	    			$gids = explode(',', $this->configData['group_id']);
	    			return $this->alarmModel->updateUserGroup($uid,$gids);
	    		} else {
	    			return $this->alarmModel->deleteUserGroup($uid);
	    		}
	    	}
	    	return $r;
    	}
    	return FALSE;
    }

    private function loadModel() {

        return new Model_Alert_Edit();
    }

}