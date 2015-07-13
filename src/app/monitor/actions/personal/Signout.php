<?php
/**
 * 个人登出动作
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     actions/personal
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Action_Signout extends Yaf_Action_Abstract
{
    public function execute() {
        $accountModel = new Model_User_Account();

        $user = User_Session::getLoginSession();

        $accountModel->logout($user);

        $this->redirect('/personal/signin');
    }

}