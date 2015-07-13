<?php
class Controller_Analytics extends Yaf_Controller_Abstract
{
    public $actions = array(
        "accessnumber"  => "modules/Api/actions/analytics/AccessNumber.php",
        "clicknumber"  => "modules/Api/actions/analytics/ClickNumber.php",
        "clicktrend"    => "modules/Api/actions/analytics/ClickTrend.php",
        "accesslist"    => "modules/Api/actions/analytics/AccessList.php",
        "accesstrend"   => "modules/Api/actions/analytics/AccessTrend.php",
        "errortrend"    => "modules/Api/actions/analytics/ErrorTrend.php",
        "errortopten"   => "modules/Api/actions/analytics/ErrorTopTen.php"
    );
}