<?php
/**
 * 监控设定
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     actions/index
 * @since       Version 1.0 @2014-06-13
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Action_Setting extends Controller_Application {

    protected $layout = 'main';
    protected $settingModel;
    protected $businessModel;
    public $lifeTime = 86400;


    public function run()
    {
        $this->settingModel = new Model_User_Setting();
        $this->businessModel = new Model_User_Business();

        if ($this->_request->isGet()) {
            $this->show();
        } else if ($this->_request->isPost()) {
            $this->modify();
        }
    }

    public function show()
    {
        $business = $this->businessModel->get('', '');

        $settings = isset($_COOKIE['url_ids'])?explode(',',$_COOKIE['url_ids']):array();

        $newBusiness = array();
        foreach ($business AS $key => $value) {
            $value['status'] = 'close';
            $newBusiness[$value['id']] = $value;
        }
        unset($business);

        foreach ($settings AS $setting) {
            $newBusiness[$setting]['status'] = 'open';
        }

        $newBusiness = array_values($newBusiness);

        $this->getView()->assign("business", $newBusiness);
    }

    public function modify()
    {
        $business = $this->_request->getPost('business');
        $status = $this->_request->getPost('status');
        $result = false;

        $result = setcookie("url_ids", $business, time() + 3600 * 24, '/');

        /*if ($status == 'close') {
            $result = $this->settingModel->closeBusiness($this->loginUser['id'], $business);
        } else if ($status == 'open') {
            $result = $this->settingModel->openBusiness($this->loginUser['id'], $business);
        }*/

        if ($result) {
            Ym_CommonTool::output($this, array('result' => 'success'), "json");
        } else {
            Ym_CommonTool::output($this, array('result' => 'failed'), "json");
        }
    }
}