<?php
class Service_Monitor_Alarm
{
    const TYPE_WARNING  = "warning";
    const TYPE_CRITICAL = "critical";

    private $monitorAlarmModel;

    public function __construct() {
        $this->monitorAlarmModel = new Model_Monitor_Alarm();
    }

    public function getCount($type = 0) {
        $returns = 0;

        switch ($type) {
            case self::TYPE_WARNING :
                $returns = $this->monitorAlarmModel->getCount(Model_Monitor_Alarm::WARNING);
                break;
            case self::TYPE_CRITICAL :
                $returns = $this->monitorAlarmModel->getCount(Model_Monitor_Alarm::CRITICAL);
                break;
            default :
                $returns = $this->monitorAlarmModel->getCount();
                break;
        }

        return $returns;
    }
} 