<?php
/**
 * 用户账户模型
 *
 * 根据条件获取用户信息
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     models/user
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Model_User_Account {

    public $userTable;

    public function __construct() {
        $this->userTable = Sys_Database::getTable('user');
    }

    /**
     * 获取用户信息
     * @param <array> $account 账号密码
     * @return 用户信息
     */
    public function getUser($account) {
        $dao = new Ym_Dao('default');
        $account['password'] = md5(md5($account['password']) . User_Session::LOGIN_SALT);
        str_replace("'", "", $account['email']);
        $sql = "SELECT * FROM {$this->userTable} WHERE email = '" . $account['email'] . "' AND password = '" . $account['password'] . "'";
        $result = $dao->queryRow($sql, true);
        return $result;
    }

    /**
     * 登陆
     *
     * @param $user
     */
    public function login($user) {

        // 存储用户信息到Session
        User_Session::setLoginSession($user);
        // 存储用户信息到Cookie
        User_Session::setLoginCookie($user);

        User_Session::setUserIdCookie($user['id']);

        $serviceUserOnline = new Model_User_Online();

        // 新增在线用户
        $userOnline = array(
            'uid'         => $user['id'],
            'aliasName'   => $user['aliasname'],
            'fullName'    => $user['fullname'],
            'email'       => $user['email'],
            'ip'          => Ym_CommonTool::get_ip()
        );
        $serviceUserOnline->addUser($userOnline);
    }

    public function logout($user) {
        $serviceUserOnline = new Model_User_Online();
        // 将用户从在线列表移出
        $serviceUserOnline->delUser($user['id']);

        // 用户登出
        User_Session::delLoginSession();
        User_Session::delLoginCookie();
        User_Session::delUserIdCookie();

        $notice = array('msg' => '您已成功登出！', 'type' => 'information');
        User_Session::setNotice($notice);
    }
}