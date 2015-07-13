<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang<Fransis.shang@yeahmobi.com>
 * Date: 14-7-29
 * Time: 上午11:34
 */

class Action_Logeditok extends Controller_Application {

//    protected $layout = 'main';

    public $configData = array();

    # 实例化 报警model
    public function loadModel(){
        return  new Model_Alarm_Log();
    }

    public function run() {
//    	$optionType = $this->getRequest()->getPost('option_type','list');
    	$this->configData = $this->getRequest()->getPost();
        //print_r($this->configData);
        //exit;

    	$this->alarmModel = $this->loadModel();
//        print_r($this->configData);exit;
//    	$optionType = isset($this->configData['option_type']) ? $this->configData['option_type'] : 'list';
//        echo $optionType;exit;
        #保存数据 调用保存方法后 退出
//        if ($optionType == 'save'){

         $this->saveData();
//            exit;
//        }
    }

    # 保存数据
    private function saveData() {

        $r = FALSE;
        if ($this->configData) {
            if(empty($this->configData['logid'])) {
                //var_dump($this->configData);exit;

                //批量插入
                $hostnames = explode('#',trim($this->configData['hostname']));
                $host_ips = explode('#',trim($this->configData['hostip']));
                $_config = $this->configData;
                if ( (count($host_ips) > 0) AND (count($hostnames)==count($host_ips)) ) {
                    foreach($hostnames as $k=>$hostname) {
                        $region_arr = explode('-',$hostname);
                        $region = isset($region_arr[1]) ? $region_arr[1] : NULL;
                        if (!$region) continue;

                        $region_code = $this->getRegion($region);
                        $_config['region'] = $_config['region'] ? $_config['region'] : $region_code;
                        $_config['hostname'] = $hostname;
                        $_config['hostip'] = ip2long($host_ips[$k]);

                        $log_worker_action = $this->configData['config'];
                        unset($_config['config'],$_config['logid']);
                        $insertId = $this->alarmModel->saveData($_config);
                        $r = $this->alarmModel->insertLogconfigActions($insertId,$log_worker_action);
                    }
                }
                /*$this->configData['hostip'] = ip2long($this->configData['hostip']);
                $log_worker_action = $this->configData['config'];

                unset($this->configData['config'],$this->configData['logid']);

                $insertId = $this->alarmModel->saveData($this->configData);
                $r = $this->alarmModel->insertLogconfigActions($insertId,$log_worker_action);*/

            } else {

                $where = 'logid='.$this->configData['logid'];
                $types = array();
                $this->configType = array();

                $this->configData['hostip'] = ip2long($this->configData['hostip']);

                $r = $this->alarmModel->updateLogconfigActions($this->configData['logid'],$this->configData['config']);

                unset($this->configData['config'],$this->configData['logid']);
                if($r) $r =  $this->alarmModel->updateData($this->configData, $where);
            }
        }
        if ($r) {
            static::output(1,'Query is OK!',$r);
        }
        static::output(0,'Query is error!',$r);
    }


    public function getRegion($str='') {

        if (!$str) return FALSE;

        $a = array('IAD'=>1,'SIN'=>2,'NCA'=>3,'SP'=>5,'LON'=>11);
        return isset($a[$str]) ? $a[$str] : FALSE;
    }
}