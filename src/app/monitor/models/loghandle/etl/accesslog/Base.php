<?php

abstract class Model_Loghandle_Etl_Accesslog_Base extends Model_Loghandle_Etl_Base
{
    public $logType;
    public $cacheModel;

    public function __construct($logType) {

        parent::__construct($logType);

        $this->logType = $logType;

        $config = Yaf_Registry::get('monitor_config');

        $cacheName = $config['loghandle'][$this->logType]['cache'];

        $this->cacheModel = Model_Loghandle_Cache_Factory::create($this->logType, $cacheName);
    }
}