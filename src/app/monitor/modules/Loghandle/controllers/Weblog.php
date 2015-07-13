<?php

/**
 * Class Controller_Weblog
 */
class Controller_Weblog extends Yaf_Controller_Abstract
{
    public $logType = 'weblog';

    public function startAction() {
        $config    = Yaf_Registry::get('monitor_config');
        $dataName  = $config['loghandle'][$this->logType]['data'];
        $stepTime  = intval($config['loghandle'][$this->logType]['step']);

        $logPersistentService = new Service_Log_Persistent();

        $logApp = $logPersistentService->getAppId();

        foreach ( $logApp AS $table => $appId) {
            $pid = pcntl_fork();

            if (! $pid) {
                $logDataService = new Service_Log_Data($this->logType, $dataName);

                $dataResult = $logDataService->getWeblog($table, $stepTime);

                if ( ! $dataResult ) {
                    return false;
                }


                $etlModel = Model_Loghandle_Etl_Factory::create($this->logType, 'key');

                $extraData = array(
                    'logapp' => $logApp,
                    'data'   => $dataResult,
                    'table'  => $table
                );

                $transData = $etlModel->transform($extraData);

                $firstData = current($dataResult);
                $time      = $firstData['time']->sec;

                $etlModel->load($transData, $time);

                exit;
            }
        }

        return false;
    }
}