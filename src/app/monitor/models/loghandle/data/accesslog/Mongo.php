<?php

/**
 * Class Model_Loghandle_Data_Accesslog_Mongo
 */
class Model_Loghandle_Data_Accesslog_Mongo extends Model_Loghandle_Data_Base
{
    public $collections;

    public $mongoDao;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->mongoDao = new Dao_Mongo($this->logType);
    }

    public function getAfter($table, $time, $slave = false) {
    	$returns = false;
    	$timeout = 30;
    	$slaveLimit = 10;
    	
    	if ($slave) {
    	
    		$d = $this->getData($table, $time, $time);
    		if (count($d) < $slaveLimit) return TRUE;
    	}

        for ($i = 0; $i < $timeout; $i++) {
            // 获取范围外数据

            $this->mongoDao = new Dao_Mongo($this->logType);
            $this->mongoDao->orderBy(array("time" => -1));
            $this->mongoDao->limit(1);
            $data = $this->mongoDao->get($table);

            if ( count($data) < 1) {

            	$returns =  true;
            } else {
                
            	$lastData = current($data);
                $lastTime = isset($lastData['time']) ? $lastData['time']->sec : 0;
                
                if ((int)(abs($lastTime-$time)) >= $timeout) {
                	$returns = true;
                    break;
                }
                
                if ($lastTime < $time) {
                	Ym_Logger::error(date('Y-m-d H:i:s',$time).' need wait for '.$table.' '.date('Y-m-d H:i:s',$lastTime));
                }
                
                if ($lastTime > $time) {
                	$returns = true;
                    break;
                } else {
                	sleep(1);
                	continue;
                }
            }
        }

        return $returns;
    }

    public function getData($table, $beginTime, $endTime)
    {
        $data = null;

        // 获取要转换的数据集
//        $this->mongoDao->whereGt("time", new MongoDate($beginTime));
//        $this->mongoDao->whereLte("time", new MongoDate($endTime));
        $this->mongoDao->where(array("time" => new MongoDate($endTime)));

        $data = $this->mongoDao->get($table);

        return $data;
    }
} 