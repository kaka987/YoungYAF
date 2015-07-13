<?php
class Controller_User extends Yaf_Controller_Abstract
{
    public $params = null;

    public function init() {
        $this->params = $this->getRequest()->getParams();

    }

    public function businessAction() {
        $output = null;
        $uid    = $this->getRequest()->getQuery('uid');

        $serviceUserBusiness = new Service_User_Business();

        if (array_key_exists('list', $this->params)) {
            $output = $serviceUserBusiness->getList($uid);
        }

        Sys_Common::output(true, '', $output);
    }

    public function onlineAction() {
        $output = null;
        $uid    = $this->getRequest()->getQuery('uid');

        $serviceUserOnline = new Service_User_Online();

        if (array_key_exists('refresh', $this->params)) {
            if (empty($uid)) {
                return false;
            }

            $output = $serviceUserOnline->refresh($uid);

            if($output) {
                Sys_Common::output(true, 'refresh success', $output);
            } else {
                Sys_Common::output(false, 'refresh failed', $output);
            }
        } else if (array_key_exists('total', $this->params)) {
            $output = $serviceUserOnline->getTotal();
            Sys_Common::output(true, '', $output);
        }

        return false;
    }

} 