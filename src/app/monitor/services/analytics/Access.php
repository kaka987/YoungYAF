<?php

class Service_Analytics_Access
{
    const INDEX_PV     = 'pv';
    const INDEX_UV     = 'uv';
    const INDEX_IP     = 'ip';
    const INDEX_ERROR  = 'error';
    const INDEX_PEAK   = 'peak';
    const INDEX_TROUGH = 'trough';
    const LIST_ACCESS  = 'access';
    const LIST_SIZE    = 'size';
    const LIST_TIMES   = 'times';
    const CACHE_PATH   = '/tmp/';

    const PAGE_NUM = 10;

    private $code;

    public $loghandlePersistentRecordModel;
    public $loghandlePersistentAccesslogModel;

    public function __construct() {
        $this->loghandlePersistentRecordModel    = new Model_Loghandle_Persistent_Record();
        $this->loghandlePersistentAccesslogModel = new Model_Loghandle_Persistent_Accesslog();

        $this->code = array(500,502,504,505);
    }

    /**
     * @param $hosts
     * @param $index
     * @param $from
     * @param $to
     * @return int
     */
    public function getIndex($hosts, $index, $from, $to) {
        $returns = 0;

        $analyticsIndexModel = new Model_Analytics_Index();

        $codeStr = implode(",", $this->code);

        switch ($index) {
            case self::INDEX_PV :
                $returns = $analyticsIndexModel->getPageVisit($hosts, $from, $to);
                break;
            case self::INDEX_UV :
                $returns = $analyticsIndexModel->getUserVisit($hosts, $from, $to);
                break;
            case self::INDEX_IP :
                $returns = $analyticsIndexModel->getUserVisit($hosts, $from, $to);
                break;
            case self::INDEX_ERROR :
                $returns = $analyticsIndexModel->getErrorNumber($hosts, $from, $to);
                break;
            case self::INDEX_PEAK :
                $returns = $this->getRequestMinute('max', $hosts, $from, $to);
                break;
            case self::INDEX_TROUGH :
                $returns = $this->getRequestMinute('min', $hosts, $from, $to);
                break;
        }

        return $returns;
    }

    /**
     * @param $type
     * @param $hosts
     * @param $from
     * @param $to
     * @param $page
     * @param $condition
     * @return array
     */
    public function getList($type, $hostPathID, $path, $from, $to, $page = 1, $condition = array()) {
        $returns = array();
        $analyticsModel = null;

        switch ($type) {
            case self::LIST_ACCESS :
                $analyticsModel = new Model_Analytics_Access();
                break;
            case self::LIST_SIZE :
                $analyticsModel = new Model_Analytics_Size();
                break;
            case self::LIST_TIMES :
                $analyticsModel = new Model_Analytics_Times();
                break;
        }

        $errorNum = 1;
        if ($hostPathID) $errorNum = $analyticsModel->getExistError($hostPathID , $path, $from, $to, $condition);
        //var_dump($errorNum);exit;
        if (empty($errorNum)){
            $returns['count'] = 0;
            $returns['data']  = '';
            return $returns;
        }
        $count = ceil($analyticsModel->getCount($hostPathID , $path, $from, $to, $condition) / self::PAGE_NUM);

        if ($page < 1) $page = 1;
        if (!empty($count) && $page > $count) $page = $count;

        $limit = self::PAGE_NUM;
        $skip  = ($page - 1) * $limit;

        $returns['count'] = $count;
        $returns['data']  = $analyticsModel->getList($hostPathID, $path, $from, $to, $skip, $limit, $condition);

        return $returns;
    }

    /**
     * @param $flag
     * @param $hosts
     * @param $from
     * @param $to
     * @return int
     */
    public function getRequestMinute($flag, $hosts, $from, $to) {
        $returns = 0;

        $analyticsRequestModel = new Model_Analytics_Request();
        $returns = $analyticsRequestModel->getMinute($flag, $hosts, $from, $to);

        return $returns;
    }

    public function getAccessTrend($hosts, $from, $to) {
        $returns = array();


        // 原始起点
        $originFrom = $from;
        // 查询天数
        $day = ($to - $from) / 86400;

        $recordTime = $this->loghandlePersistentRecordModel->getRecordTime('accesslog', 60);

        for($i = $day; $i >= 1; $i--) {
            $to     = $originFrom + ($i * 86400);
            // 某天起点
            $from   = $to - 86400;
            $to     = $i == $day ? $to = $recordTime - 60 : $to;

            $name      = date('m/d', $from);
            $cacheName = md5($name.$hosts);

            $useCache = false;

            if ($i != $day) {
                $cache = $this->getCache($cacheName);
                if (! empty($cache)) {
                    $useCache = true;
                }
            }

            // 某天某分钟起点
            $time = $from;

            $trendData = array();

            if ($useCache) {
                $trendData = $cache;
            } else {
                while($time < $to) {
                    $result = $this->loghandlePersistentAccesslogModel->getAccessTrend($hosts, $from, $to, $time);

                    $trendData = array_merge($trendData, $result);

                    $time += 60;
                }
            }

            $data = array();

            foreach ($trendData as $value) {
                $value['time'] = $i == 1 ? $value['time'] : $value['time'] - (($i - 1) * 86400);
                $data[] = array('x' => $value['time'] * 1000, 'y' => intval($value['num']));
            }

            $returns[] = array(
                'name'  => $name,
                'data'  => $data,
            );

            if ($i != $day) {
                $this->putCache($cacheName, $trendData);
            }

        }

        return $returns;
    }

    public function getErrorTrend($hosts, $from, $to) {
        $returns = array();

        $codeStr  = implode(",", $this->code);

        $to = empty($to) ? $to : strtotime(date('Y-m-d H:i',$to))-1;
        $result = $this->loghandlePersistentAccesslogModel->getErrorTrend($hosts, $from, $to, $codeStr);

        $data  = array();
        $path  = array();
        $index = array();
        $num   = array();
        //var_dump($result);exit;
        $tmp = $t = array();
        foreach ($result as $k=>$r) {

            $min = strtotime(date('Y-m-d H:i',$r['time']));
            $key = $min.'-_-'.$r['code'].'-_-'.$r['host'].'-_-'.$r['path'];
            $tmp[$key] = isset($tmp[$key]) ? $tmp[$key] += $r['num'] : $r['num'];
        }

        $i=0;
        foreach($tmp as $k=>$r) {

            $d = explode('-_-',$k);
            $t[$i]['time'] = $d[0];
            $t[$i]['code'] = $d[1];
            $t[$i]['host'] = $d[2];
            $t[$i]['path'] = $d[3];
            $t[$i]['num']  = $r;
            $i++;
        }

        foreach ($t AS $row) {
            if (in_array($row['code'], $this->code)) {

                $key = $row['code'] . $row['time'];

                $num[$key] = isset($num[$key]) ? $num[$key] + $row['num'] : $row['num'];
                $path[$key][$num[$key]] = array($row['host'], $row['path'], $row['num']);



                $index[$key] = array(
                    "status" => $row['code'],
                    "time"   => $row['time'],
                    "num"    => $num[$key]
                );
            }
        }

        foreach ($index AS $key => $val) {
            ksort($path[$key]);
            $path[$key] = array_slice($path[$key], 0, 3);
            $data[$val['status']][] = array(
                "time" => $val['time'],
                "num"  => $val['num'],
                "top"  => $path[$key]
            );
        }

        $returns = $data;

        return $returns;
    }

    public function getErrorTopTen($hosts, $from, $to) {
        $returns = array();

        $code  = array(400,403,404,499,500,502,504,505);

        $status  = implode(",", $code);

        $returns = $this->loghandlePersistentAccesslogModel->getErrorTopTen($hosts, $from, $to, $status);

        return $returns;
    }
    
    public function getCache($cacheName='') {
    	
    	$file = self::CACHE_PATH.$cacheName;
    	if (file_exists($file)) return unserialize(file_get_contents($file));
    	return '';
    }
    
    public function putCache($cacheName='',$data='') {
    	
    	return file_put_contents(self::CACHE_PATH.$cacheName, serialize($data));
    }
} 
