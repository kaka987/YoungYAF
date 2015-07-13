<?php
class Service_Log_Data
{
    private $dataModel;
    private $logPersistentService;
    private $logType;

    public function __construct($logType, $name) {
        $this->logType              = $logType;
        $this->dataModel            = Model_Loghandle_Data_Factory::create($logType, $name);
        $this->logPersistentService = new Service_Log_Persistent();
    }

    public function getAccessLog($tables, $stepTime) {
        $returns    = array();
        $nodes      = count($tables);
        $slave      = false;

        $recordTime = $this->logPersistentService->getRecordTime($this->logType, $stepTime);
        $from       = intval($recordTime);
        $to         = intval($from + $stepTime);

        foreach ($tables AS $n => $table) {
            if ( ($nodes > 2) AND ($n == ($nodes - 1)) ) $slave = true;

            $after = $this->dataModel->getAfter($table, $to, $slave);

            if ( ! $after ) {
                Ym_Logger::error('Access after false endTime: '.date('Y-m-d H:i:s',$to));
                break;
//                return false;
            }
        }

        $this->logPersistentService->updateRecordTime($this->logType, $to);

        foreach ($tables AS $table) {
            $result  = $this->dataModel->getData($table, $from, $to);
            $returns = array_merge($returns, $result);
        }

        return $returns;
    }

    public function getWeblog($table, $stepTime) {
        $returns    = array();
        $recordName = $this->logType . "." . $table;
        $recordTime = $this->logPersistentService->getRecordTime($recordName, $stepTime);
        $from       = intval($recordTime);
        $to         = intval($from + $stepTime);

        $to         = $this->dataModel->getAfter($table, $from);

        if (! $to) {
            return false;
        }

        $returns = $this->dataModel->getData($table, $from, $to);

        $this->logPersistentService->updateRecordTime($recordName, $to);

        return $returns;
    }
}