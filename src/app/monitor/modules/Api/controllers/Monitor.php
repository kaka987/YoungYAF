<?php
class Controller_Monitor extends Yaf_Controller_Abstract
{
    public $actions = array(
        "datanode" => "modules/Api/actions/monitor/DataNode.php",
        "clickhour" => "modules/Api/actions/monitor/ClickHour.php",
        "dynamic"  => "modules/Api/actions/monitor/Dynamic.php",
        "clickdynamic"  => "modules/Api/actions/monitor/ClickDynamic.php",
        "alarm"    => "modules/Api/actions/monitor/Alarm.php",
        "getconfig"    => "modules/Api/actions/monitor/GetConfig.php"
    );
}