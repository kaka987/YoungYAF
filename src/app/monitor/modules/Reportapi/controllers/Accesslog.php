<?php

/**
 * Lbnginx data view
 *
 * @author      Xuexb<jeffxiaobo@foxmail.com>
 * @package     xiaobo
 * @since       Version 1.0.1 @20140428
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Controller_Accesslog extends Yaf_Controller_Abstract
{
	public $actions;

    public function init() {
    	$classExtd = explode('_', __CLASS__);
        $className = strtolower($classExtd[1]);

		$this->actions = Ym_Config::getAppItem('actions:'.$className);
    }
}