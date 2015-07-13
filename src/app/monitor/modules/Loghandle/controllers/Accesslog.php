<?php
/**
 * Class Controller_Accesslog
 */
class Controller_Accesslog extends Yaf_Controller_Abstract
{
    private $logType = "accesslog";

    public function startAction() {
        $config     = Yaf_Registry::get('monitor_config');
        $dataName   = $config['loghandle'][$this->logType]['data'];
        $stepTime   = intval($config['loghandle'][$this->logType]['step']);
        $modes      = explode(',', $config['loghandle'][$this->logType]['mode']);
        $tables     = explode(",", trim($config['mongo'][$this->logType]['tables']));

        $logDataService = new Service_Log_Data($this->logType, $dataName);

        $dataResult = $logDataService->getAccessLog($tables, $stepTime);

        if ( ! $dataResult) {
            return false;
        }

        $firstData = current($dataResult);
        $time      = $firstData['time']->sec;
        $modeNum   = count($modes);

        $loghandleEtlParserModel = new Model_Loghandle_Etl_Parser();
        $extractData = $loghandleEtlParserModel->extract($dataResult);

        for ($i = 0; $i < $modeNum; $i++) {
            $pid  = pcntl_fork();
            $mode = $modes[$i];

            if ( ! $pid ) {
                $etlModel  = Model_Loghandle_Etl_Factory::create($this->logType, $mode);

                $transData = $etlModel->transform($extractData);

                $etlModel->load($transData, $time);

                exit;
            }
        }

        return false;
    }
} 