<?php
/**
 * 访问控制器
 *
 * @author      Fransis<Fransis.shang@yeahmobi.com>
 * @package     controllers
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Controller_Produce extends Yaf_Controller_Abstract
{
//    public $actions = array(
//        'index'   => 'actions/index/Index.php',
//        'setting' => 'actions/index/Setting.php',
//        'access'  => 'actions/index/Accessinfo.php'
//    );
    public $actions = array(
        'produceconfig'  => 'actions/produce/ProduceConfig.php',
//        'index'  => 'actions/produce/Index.php'
    );
}
