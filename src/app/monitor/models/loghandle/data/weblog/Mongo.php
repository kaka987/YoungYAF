<?php

/**
 * Class Model_Loghandle_Data_Weblog_Mongo
 */
class Model_Loghandle_Data_Weblog_Mongo extends Model_Loghandle_Data_Base
{
    public $collections;

    public $mongoDao;

    public function __construct($logType)
    {
        parent::__construct($logType);

        $this->mongoDao = new Dao_Mongo($this->logType);
    }

    public function getAfter($table, $time) {

        $returns = false;

        $this->mongoDao = new Dao_Mongo($this->logType);
        // 获取范围外数据
//        $this->mongoDao->whereGte("time", new MongoDate($time));
//        $this->mongoDao->limit(1);

        $this->mongoDao->orderBy(array("time" => -1));
        $this->mongoDao->limit(1);

        $data = $this->mongoDao->get($table);

        if ( empty($data) ) {
            return false;
        }

        $lastData = current($data);
        $lastTime = $lastData['time']->sec;
        $diffTime = $lastTime - $time + 1;

        if ( $diffTime > 0 ) {
            $returns = $lastTime;
        }

        return $returns;
    }

    public function getData($table, $beginTime, $endTime) {
        $data = null;

        // 获取要转换的数据集
        $this->mongoDao->whereGt("time", new MongoDate($beginTime));
        $this->mongoDao->whereLte("time", new MongoDate($endTime));

        $data = $this->mongoDao->get($table);

        return $data;
    }
} 