<?php
class Action_Dynamic extends Yaf_Action_Abstract
{
    static private $mongoObj = NULL;
    private $collections = NULL;
    private $D = array();
    private $config;
    private $o=NULL;

    public $logType = 'accesslog';

    public function execute() {

        //$this->config = Yaf_Registry::get('monitor_config');
        //$this->getMongoObj();
        //$this->getCollections();
        $hosts= trim($this->getRequest()->getQuery('host'));
        $time = isset($_GET['current_time']) ? $_GET['current_time'] : time()-1;
        $step = isset($_GET['limit']) ? $_GET['limit'] : 60;
        $q 	  = isset($_GET['q']) ? $_GET['q'] : 1;
        $startTime = 0;

        $this->o = new Model_Alarm_DataNode();

        /*if ($q <= 1) {
            $lastTime = $this->getLastTime();
            $time = $lastTime - $step - 1;
            $r = $this->getDataFromMongo($time, $step);
        } else {
            $r = $this->getDataFromMongo($time, $step-1);
        }*/

        //if ($r === FALSE) Sys_Common::output(true, 'need try again!');

        if ($q <=1 )
            $start = $time-70;
        else
            $start = $time;

        $r = $this->getDataFromMysql($start,$step,$hosts);
        if ($r === FALSE) Sys_Common::output(true, 'need try again!');

        $outdata = array(
            'name' => 'access_dynamic',
            'series' => null,
            'next_time' => null
        );

        for ($i=$start;$i<($start + $step);$i++) {

            $data_tmp['time'] = $i;
            $data_tmp['num'] = isset($this->D[$i]) ? $this->D[$i]['num'] :  0;
            $outdata['series'][] = $data_tmp;
        }

        $outdata['next_time'] = $i;

        Sys_Common::output(true, 'OK!', $outdata);
    }

    public function getDataFromMysql($time,$step,$hosts='') {

        $data = array();
        $return = 0;
        $dataT = $data_tmp = array();

        $data = $this->o->getDataBySeconds($time,$step,$hosts);

        if ($data) {

            foreach ($data as $v) {

                $timed = $v['time'];
                isset($this->D[$timed]) ?  $this->D[$timed]['num'] += $v['num'] : $this->D[$timed]['num'] = $v['num'];
            }
        }
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