<?php
/**
 * 个人账户控制器
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     controllers
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Controller_Personal extends Yaf_Controller_Abstract {

    public $actions = array(
        'index'  => 'actions/personal/Index.php',
        'signin' => 'actions/personal/Signin.php',
        'signout' => 'actions/personal/Signout.php'
    );

}