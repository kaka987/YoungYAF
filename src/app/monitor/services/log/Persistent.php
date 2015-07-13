<?php
class Service_Log_Persistent
{
    private $loghandlePersistentRecordModel;
    private $loghandlePersistentWeblogModel;
    private $loghandlePersistentAccesslogModel;

    const TYPE_ACCESS_TREND = "access_trend";
    const TYPE_ERROR_TREND  = "error_trend";

    public function __construct() {
        $this->loghandlePersistentRecordModel    = new Model_Loghandle_Persistent_Record();
        $this->loghandlePersistentWeblogModel    = new Model_Loghandle_Persistent_Weblog();
        $this->loghandlePersistentAccesslogModel = new Model_Loghandle_Persistent_Accesslog();
    }

    public function getRecordTime($recordName, $stepTime = 60) {
        $returns = null;

        $returns = $this->loghandlePersistentRecordModel->getRecordTime($recordName, $stepTime);

        return $returns;
    }

    public function updateRecordTime($recordName, $time) {
        $returns = null;

        $returns = $this->loghandlePersistentRecordModel->updateRecordTime($recordName, $time);

        return $returns;
    }

    public function getAppId() {
        $returns = null;

        $returns = $this->loghandlePersistentWeblogModel->getAppId();

        return $returns;
    }

    public function getData($type, $option) {
        $returns = array();

        $hosts  = $option['hosts'];
        $from   = empty($option['from']) ? "" : $option['from'];
        $to     = empty($option['to']) ? "" : $option['to'];
        $status = empty($option['status']) ? "" : $option['status'];

        switch ($type) {
            case self::TYPE_ACCESS_TREND:
                $returns = $this->loghandlePersistentAccesslogModel->getAccessTrend($hosts, $from, $to);
                break;
            case self::TYPE_ERROR_TREND:
                $returns = $this->loghandlePersistentAccesslogModel->getErrorTrend($hosts, $from, $to, $status);
                break;
        }

        return $returns;
    }
} 