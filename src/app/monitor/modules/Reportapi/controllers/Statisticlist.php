<?php
/***
 * Class Controller_Statisticlist
 */
class Controller_Statisticlist extends Yaf_Controller_Abstract {

    public $actions;
    /*array(
        'requestdatasize'  		 => 'modules/Reportapi/actions/statisticlist/RequestDataSize.php',
    );*/
    public function init() {
        $classExtd = explode('_', __CLASS__);
        $className = strtolower($classExtd[1]);
        $this->actions = Ym_Config::getAppItem('actions:'.$className);
//        print_r($this->actions);
//        exit;
    }
} 