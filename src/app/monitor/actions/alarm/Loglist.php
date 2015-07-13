<?php
class Action_Log extends Controller_Application {

 	protected $layout = 'main';
 	
 	public $configData = array();

//    private function loadModel() {
//        return new Model_Alarm_Log();
//    }

    # 实例化 报警model
    public function loadModel(){
        return  new Model_Alarm_Log();
    }

    public function run() {
    	
//    	$optionType = $this->getRequest()->getPost('option_type','list');
//    	$this->configData = $this->getRequest()->getPost();
   	    $this->alarmModel = $this->loadModel();
//
//    	$optionType = isset($this->configData['option_type']) ? $this->configData['option_type'] : 'list';
//
//        #保存数据 调用保存方法后 退出
//        if ($optionType == 'save'){
//            $this->saveData();
//        }

        # 分页  并渲染 展示数据
    	$page = $this->getRequest()->getQuery('p');
    	$alarmList['page'] = $page ? $page : 1;
    	$limit = 10;
    	
    	$alarmList['list'] = $this->alarmModel->selectData(NULL, ($alarmList['page']-1)*$limit, $limit);
    	$alarmNum = $this->alarmModel->selectData(NULL, -1);
    	$alarmList['num'] = $alarmNum['num'];
//        print_r($alarmList);
    	if(empty($alarmList['list'])){
//            echo 'hello';
            $this->alarmModel->query();
        }
    	$alarmList['pageCount'] = (int)($alarmList['num']/$limit) + 1;
    	
    	$alarmList['pageView'] = $this->page($alarmList['page'], $alarmList['pageCount']);
    	
    	$this->getView()->assign('alarmList', $alarmList);
    }

    public function page($p, $pageCount) {
    
    	$page = '';
    	$active = '';
		$pageStart = 1;
        if($p>10) {
            $pageStart = $p-9;
        }
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