<?php
class Action_Set extends Controller_Application {

    protected $layout = 'main';

    public function run() {
    	
    	$page = $this->getRequest()->getQuery('p');
    	$alarmList['page'] = $page ? $page : 1;
    	$limit = 10;
    	
    	$this->alarmModel = $this->loadModel();
    	
    	$alarmList['list'] = $this->alarmModel->selectData(NULL, ($alarmList['page']-1)*$limit, $limit);
    	$alarmNum = $this->alarmModel->selectData(NULL, -1);
    	$alarmList['num'] = $alarmNum['num'];
    	
    	$alarmList['pageCount'] = (int)($alarmList['num']/$limit) + 1;
    	$alarmList['pageView'] = $this->page($alarmList['page'], $alarmList['pageCount']);
    	
    	$logconfig = $this->alarmModel->getlogConfig();
    	$t = array();
    	if ($logconfig) {
    		foreach ($logconfig as $v) {
    			
    			$t[$v['id']] = $v['monitor_app'];
    		}
    	}
    	$alarmList['log'] = $t;
    	
    	$this->getView()->assign('alarmList', $alarmList);
    }

    private function loadModel() {
    	 
    	return new Model_Alarm_Config();
    }
    
    
    public function page($p, $pageCount) {
    
    	$page = '';
    	$active = '';
		$pageStart = 1;
        if($p>10) $pageStart = $p-9;
        $pageMax = ($pageCount<10) ? $pageCount+1 : $pageStart + 10;
        
        $page  = '<ul>';
        $page .= '<li class="previous disabled"><a href="#">&#8249;</a></li>';
        for($i=$pageStart;$i<$pageMax;$i++) {
        	if($i==$p) $active = 'active';
        	$page .= '<li><a class="'.$active.'" href="?p='.$i.'">'.$i.'</a></li>';
        }
        
        $page .= '<li class="next"><a href="#">&#8250;</a></li>';
        $page .= '</ul>';
        
        return $page;
    }

}