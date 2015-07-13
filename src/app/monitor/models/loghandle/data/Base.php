<?php

/**
 * Class Model_Loghandle_Data_Base
 */
abstract class Model_Loghandle_Data_Base
{
    public $logType;

    public function __construct($logType) {
        $this->logType = $logType;
    }

    /**
     * @param $table
     * @param $beginTime
     * @param $endTime
     * @return mixed
     */
    abstract public function getData($table, $beginTime, $endTime);

    /**
     * @param $table
     * @param $time
     * @return mixed
     */
    abstract public function getAfter($table, $time);

}