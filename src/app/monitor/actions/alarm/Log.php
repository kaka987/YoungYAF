<?php
class Action_Log extends Controller_Application {

 	protected $layout = 'main';
 	
 	public $configData = array();

    public function run() {
    	
    	$optionType = $this->getRequest()->getPost('option_type','list');
    	$this->configData = $this->getRequest()->getPost();
    	$this->alarmModel = $this->loadModel();
    	
    	$optionType = isset($this->configData['option_type']) ? $this->configData['option_type'] : 'list';
    	if ($optionType == 'save') $this->saveData();
    	
    	$page = $this->getRequest()->getQuery('p');
    	$alarmList['page'] = $page ? $page : 1;
    	$limit = 10;
    	
    	$alarmList['list'] = $this->alarmModel->selectData(NULL, ($alarmList['page']-1)*$limit, $limit);
    	$alarmNum = $this->alarmModel->selectData(NULL, -1);
    	$alarmList['num'] = $alarmNum['num'];
    	
    	$alarmList['pageCount'] = (int)($alarmList['num']/$limit) + 1;
    	
    	$alarmList['pageView'] = $this->page($alarmList['page'], $alarmList['pageCount']);
    	$this->getView()->assign('alarmList', $alarmList);
    }

    private function loadModel() {
    	 
    	return new Model_Alarm_Log();
    }
    
    private function saveData() {
    	
    	$this->display = 'none';
		if ($this->configData) {
			if(empty($this->configData['monitor_id'])) {
    			
				unset($this->configData['monitor_id'],$this->configData['option_type']);
    			$this->configData['monitor_ip'] = ip2long(trim($this->configData['monitor_ip']));
    			
    			$r = $this->alarmModel->{__FUNCTION__}($this->configData);
			} else {
				
				$where = 'id='.$this->configData['monitor_id'];
				unset($this->configData['monitor_id'],$this->configData['option_type']);
    			$this->configData['monitor_ip'] = ip2long(trim($this->configData['monitor_ip']));
    			
    			$r =  $this->alarmModel->updateData($this->configData, $where);
			}
    	}
    	
    	//生成配置文件
    	$this->generateTdConfig();
    	if ($r) static::output(1,'Query is OK!',$r);
    	static::output(0,'Query is error!',$r);
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
    
    
    public function generateTdConfig() {
    	
    	if ( ! $this->configData) return FALSE;
    	
    	$config = Ym_Config::getAppItem('alarm:monitor');
    	$filename = isset($config['region']) ? $config['region'] : 'test';
    	
    	$td_config = <<<EOF
<match mongo.*>
  type mongo
  host 172.30.10.111
  port 27017
  database fluentd
  collection test
  tag_mapped
  remove_tag_prefix mongo.
  flush_interval 1s
</match>

<source>
  type tail
  path /usr/local/apache2/logs/access_log
  pos_file /tmp/access.log.pos
  tag mongo.access
  format none
</source>
EOF;
		file_put_contents('/tmp/td-agent.conf', $td_config);
    		
    }
}