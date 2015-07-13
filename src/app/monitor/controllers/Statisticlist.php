<?php
/***
 * Class Controller_Statisticlist
 */
class Controller_Statisticlist extends Yaf_Controller_Abstract {

    public $actions = array(
        'requestdatasize'  => 'actions/statisticlist/RequestDataSize.php',
        'responsetime' => 'actions/statisticlist/ResponseTime.php',
        'accessnumber' => 'actions/statisticlist/AccessNumber.php'
    );

} 