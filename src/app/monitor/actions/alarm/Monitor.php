<?php
class Action_Monitor extends Controller_Application {

    protected $layout = 'main';

    public function run() {

        $this->triggerTable = Sys_Database::getTable('trigger');
        $this->alarmModel = $this->loadModel();

        if ($this->getRequest()->getPost('o') == 'delete') {
            $gid = $this->getRequest()->getPost('gid');
            $rlaid = $this->getRequest()->getPost('rlaid');
            $this->delete($gid,$rlaid);
        }

        if ($this->getRequest()->getQuery('o') == 'edit') {

            Yaf_Dispatcher::getInstance()->disableView();
            $this->initView();

            $configData = array();
            $id = $this->getRequest()->getQuery('id');
            $alarmConfig = new Model_Alarm_Log();
            if ($id) {

                $configDataTmp = $this->alarmModel->selectData($id,1,1,$this->triggerTable);
                $configData = $configDataTmp[0];
                $configData['selected'] = $this->alarmModel->getNames($configData['rlaid']);
            }
            $configData['logs'] = $alarmConfig->getLogs();
            $configData['worker'] = $alarmConfig->getWorkers();
            $configData['actions'] = $alarmConfig->getActions();


            $this->display('addtrigger', array('D'=>$configData));
        }

        if ($this->getRequest()->getQuery('o') == 'add') {
            $this->add();
        }

        $page = $this->getRequest()->getQuery('p');
        $alarmList['page'] = $page ? $page : 1;
        $id = $this->getRequest()->getQuery('pluginid');
        $pid = $id ? $id : NULL;
        $limit = 20;

        $alarmList['list'] = $this->alarmModel->selectData($pid, ($alarmList['page']-1)*$limit, $limit, $this->triggerTable);
        if ($alarmList['list']) {
            foreach($alarmList['list'] as $k=>$v) {

                $r = $this->alarmModel->getNames($v['rlaid']);
                $alarmList['list'][$k]['log'] = isset($r['logid']) ? $r['logid']."-".$r['service'] : '';
                $alarmList['list'][$k]['worker'] = isset($r['workerid']) ? $r['workerid']."-".$r['workername'] : '';
                $alarmList['list'][$k]['action'] = isset($r['actionid']) ? $r['actionid']."-".$r['actionname'] : '';
            }
        }
        $alarmNum = $this->alarmModel->selectData($pid, -1, -1, $this->triggerTable);
        $alarmList['num'] = $alarmNum['num'];

        $alarmList['pageCount'] = (int)($alarmList['num']/$limit) + 1;
        $alarmList['pageView'] = $this->page($alarmList['page'], $alarmList['pageCount']);

        $this->getView()->assign('alarmList', $alarmList);
    }

    private function loadModel() {

        return new Model_Alarm_Config();
    }

    public function delete($uid=0,$rlaid=0) {

        if ($uid==0) Ym_CommonTool::myoutput(0,'Query is error!');

        $this->alarmModel->deleteRLA($rlaid);
        $result =  $this->alarmModel->deleteData($uid, $this->triggerTable);
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

        return $postData;
    }

    private function getPostData($configData) {

        $arr = array('notify_method','notify_type','apps','hosts','services','user_id');

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
        for($i=$pageStart;$i<$pageMax;$i++) {
            if($i==$p) $active = 'active';
            $page .= '<li><a class="'.$active.'" href="?p='.$i.'">'.$i.'</a></li>';
        }

        $page .= '<li class="next"><a href="#">&#8250;</a></li>';
        $page .= '</ul>';

        return $page;
    }

    private function insertData() {

        $r = FALSE;
        if ($this->configData) {

            $config = $this->configData;
            foreach ($this->configData['logs'] as $logId) {
                $config['rlaid'] = $this->alarmModel->insertRLA($logId,$this->configData['workers'],$this->configData['actions']);
                if (!$config['rlaid']) return FALSE;
                unset($config['logs'],$config['workers'],$config['actions']);
                $r = $this->alarmModel->insertTriggers($config);
            }

        }
        return $r;
    }

    private function updateData() {

        $r = FALSE;

        if ($this->configData) {

            $config = $this->configData;
            if ($this->configData['rlaid'])
                $r = $this->alarmModel->deleteRLA($this->configData['rlaid']);
            if ($r) {
                foreach ($this->configData['logs'] as $logId) {
                    $config['rlaid']  = $this->alarmModel->insertRLA($logId,$this->configData['workers'],$this->configData['actions']);
                    $where = 'id='.$this->configData['id'];
                    unset($config['logs'],$config['workers'],$config['actions'],$config['id']);
                    $r =  $this->alarmModel->updateData($config, $where, $this->triggerTable);
                }
            }
        }
        return $r;
    }
}