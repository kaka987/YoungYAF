<?php

/**
 * Lbnginx data view
 *
 * @author      Fransis.shang<Fransis.shang@yeahmobi.com>
 * @package     Fransis
 * @since       Version 1.0.1 @20140428
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Controller_Accessinfo extends Yaf_Controller_Abstract
{

    public $actions;
    /*array(
        'test'            		 => 'modules/Reportapi/actions/accessinfo/Test.php',
        'accessglobal'  		 => 'modules/Reportapi/actions/accessinfo/AccessGlobal.php',
    );*/

    public function init() {
        $classExtd = explode('_', __CLASS__);
        $className = strtolower($classExtd[1]);
        $this->actions = Ym_Config::getAppItem('actions:'.$className);
//        print_r($this->actions);
//        exit;
    }
}