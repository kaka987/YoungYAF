<?php
/**
 * 监控报警服务
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     controllers/monitoralarm
 * @version     Version 1.0 2014-06-03
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Controller_Monitoralarm extends Yaf_Controller_Abstract {

	public $actions;

    public function init() {
    	$classExtd = explode('_', __CLASS__);
        $className = strtolower($classExtd[1]);

		$this->actions = Ym_Config::getAppItem('actions:'.$className);
    }
}