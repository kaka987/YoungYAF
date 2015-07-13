<?php
/***
 * Class Action_Access_trend
 *
 * @author      孙康健<chiaki.sun@yeahmobi.com>
 * @package     /modules/dataconvert/action/convert
 * @since       Version 1.0.1 @2014/6/24
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Action_AccessDynamic extends Controller_Application
{

	static public $mongoObj = NULL;
	public $collections = NULL;
	public $D = array();

    public $logType = 'accesslog';
	
    public function run() {
    	$this->config = Yaf_Registry::get('monitor_config');
    	$this->getMongoObj();
    	$this->getCollections();
    	
    	$time = isset($_GET['current_time']) ? $_GET['current_time'] : time()-1;
        $step = isset($_GET['limit']) ? $_GET['limit'] : 60;
        $q 	  = isset($_GET['q']) ? $_GET['q'] : 1;
        $startTime = 0;

//        $time = $time - 60;
        
        if ($q <= 1) {
        	$lastTime = $this->getLastTime();
        	$time = $lastTime - $step - 1;
        	$r = $this->getDataFromMongo($time, $step);
        } else {
        	//for ($i=0;$i<$step;$i++) {
        		//$lastTime = $this->getLastTime();
	        	//if ($time+$step <= $lastTime) {
		    		$r = $this->getDataFromMongo($time, $step-1);
		        	//break;
		        //}
		        //sleep(1);
        	//}
        }
        
        if ($r === FALSE) static::output(1,'need try again!');
        //if (empty($this->D)) static::output(1, 'OK!', array());
        //echo 'start:',$time,'#end:',($time+$step-1);
        $dataT = array();
        for ($i=$time;$i<($time + $step);$i++) {
        
        	$data_tmp['time'] = $i;
        	$data_tmp['num'] = isset($this->D[$i]) ? $this->D[$i] : 0;
        	$dataT[] = $data_tmp;
        }
        /*foreach ($this->D as $thetime=>$num) {
        	
        	$data_tmp['time'] = $thetime;
        	$data_tmp['num'] = $num;
        	$dataT[] = $data_tmp;
        }*/
                                                                                                 
        $outdata = array(
                'name' => 'access_dynamic',
                'series' => $dataT,
                'next_time' => $i
            );

        static::output(1, 'OK!', $outdata);
    }
    
	public function getDataFromMongo($time,$step) {
		
		$data = array();
		$return = 0;
		$dataT = $data_tmp = array();
		foreach ($this->collections AS $collection) {

			$start = new MongoDate($time);
			$end   = new MongoDate($time+$step);
			static::$mongoObj->select(array("time"));
            static::$mongoObj->whereBetween("time", $start, $end);

            $return = static::$mongoObj->get($collection);
            
			foreach($return as $k => $v) {
	    		$timed = $v['time']->sec;
	    		isset($this->D[$timed]) ?  $this->D[$timed]++ : $this->D[$timed]=1;
		    }
        }
	}
	
	
	public function getLastTime() {
		
		$maxTime = array();
		foreach ($this->collections AS $collection) {
			
        	static::$mongoObj->select(array("time"));
            static::$mongoObj->orderBy(array("time" => -1));
            static::$mongoObj->limit(1);

            $data = static::$mongoObj->get($collection);
			
            if (isset($data[0]['time']))
            	$maxTime[] = $data[0]['time']->sec;
        }

		return  min($maxTime);
	}
	
	public function getMongoObj() {
		
		if (static::$mongoObj == NULL) return static::$mongoObj = new Dao_Mongo($this->logType);
		return static::$mongoObj;
	}
	
    public function getCollections(){
    	
    	if ($this->collections === NULL) 
    		return $this->collections = explode(",", trim($this->config['mongo'][$this->logType]['tables']));

        return $this->collections; 
    }
}