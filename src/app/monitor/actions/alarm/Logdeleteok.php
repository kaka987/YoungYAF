<?php
/**
 * Created by PhpStorm.
 * User: Fransis.shang<Fransis.shang@yeahmobi.com>
 * Date: 14-7-29
 * Time: 上午11:34
 */

class Action_Logdeleteok extends Controller_Application {

    public $configData = array();

    # 实例化 报警model
    public function loadModel(){
        return  new Model_Alarm_Log();
    }

    public function run() {
        $this->configData = $this->getRequest()->getPost();
//        print_r($this->configData);
        $this->alarmModel = $this->loadModel();
        $this->delete();
    }
    # delete data by id
    private function delete() {
        $this->display = 'none';
        if(empty($this->configData['monitor_id'])){
            $r = 0;
        }else{
            $id = $this->configData['monitor_id'];
            $r = $this->alarmModel->deleteData($id);
        }
        if ($r) {
            static::output(1,'Delete is OK!',$r);
        }
        static::output(0,'Delete is error!',$r);
    }
}