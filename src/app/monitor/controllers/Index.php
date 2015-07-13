<?php
/**
 * 监控大盘控制器
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     controllers
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Controller_Index extends Yaf_Controller_Abstract
{
    public $actions = array(
        'index'   => 'actions/index/Index.php',
        'setting' => 'actions/index/Setting.php',
        'access'  => 'actions/index/Access.php'
    );
}
