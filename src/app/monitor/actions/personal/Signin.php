<?php
/**
 * 个人登入动作
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     actions/personal
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Action_Signin extends Yaf_Action_Abstract
{
    protected $layout = 'sign';

    public function execute() {

        $this->getView()->setLayout($this->layout);

        // 是否get请求
        if($this->getRequest()->isPost()) {

            $result = array('result' => 'success', 'text' => '登陆成功！');
            // 获取登陆数据
            $account = $this->getRequest()->getPost();
            // 获取登陆数长度
            $email_lenth = strlen(trim($account['email']));
            $password_length = strlen(trim($account['password']));

            // 格式检查
            if($email_lenth == 0) {
                $result['result'] = 'error';
                $result['text'] = '邮箱地址格式错误！';
                Ym_CommonTool::output($this, $result, 'json');
            }

            if($password_length < 6) {
                $result['result'] = 'error';
                $result['text'] = '密码长度不能小于6位字符！';
                Ym_CommonTool::output($this, $result, 'json');
            }
            
            // 查询用户信息是否存在
            $accountModel = new Model_User_Account();
            $user = $accountModel->getUser($account);

            // 如果用户信息不为空
            if(!empty($user)) {
                // 序列化用户信息到字符串
                $user['loginTime'] = time();

                $accountModel->login($user);
            } else {
                $result['result'] = 'error';
                $result['text'] = '用户名或密码不正确！';
            }

            Ym_CommonTool::output($this, $result, 'json');
        }
    }
}