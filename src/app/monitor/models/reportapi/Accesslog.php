<?php

/**
 * nginxlog data view
 *
 * @author      Fransis.shang<Fransis.shang@yeahmobi.com>
 * @package     xiaobo
 * @since       Version 1.0.1 @20140509
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_ReportApi_Accesslog
{
	/**
	 * statistics table
	 * @var string
	 */
	public $datawareTimesTable;
    public $datawareIpTable;
    public $datawareStatusTable;
    public $datawareCountryTable;
    public $relationCountryTable;
    public $relationHostTable;
    public $relationPathTable;
    public $extractRecordTable;
    public $relationBusinessTable;
    public $userBusinessTable;

    public static $dao = null;

    /**
     * 单例模式
     * @return null or object
     *
     */
    public static function getIns(){
        if(self::$dao == null){
            self::$dao = new Ym_Dao('default');
        }
        return self::$dao;
    }

    /**
     * 构造函数 防止重写
     */
    final public function __construct(){
        $this->datawareTimesTable    = Sys_Database::getTable('dataware_times');
        $this->datawareIpTable       = Sys_Database::getTable('dataware_ip');
        $this->datawareStatusTable   = Sys_Database::getTable('dataware_status');
        $this->datawareCountryTable  = Sys_Database::getTable('dataware_country');
        $this->relationCountryTable  = Sys_Database::getTable('relation_country');
        $this->relationHostTable     = Sys_Database::getTable('relation_host');
        $this->relationPathTable     = Sys_Database::getTable('relation_path');
        $this->extractRecordTable    = Sys_Database::getTable('extract_record');
        $this->relationBusinessTable = Sys_Database::getTable('relation_business');
        $this->userBusinessTable     = Sys_Database::getTable('user_business');
    }

    /**
	 * 获取访问数model
	 * @param $where
	 * @return 失败返回
	 */
	public function getAccessTrend($where)
	{
		$sql = "SELECT `time`,sum(`num`) AS num FROM `{$this->datawareTimesTable}` {$where} GROUP BY `time` ORDER BY `time` ASC";
//		$dao = new Ym_Dao('default');
		$ret = self::getIns()->fetchAll($sql, false, true);
		return $ret;
	}

	/**
	 * 获取错误趋势
	 * @param $where
	 * @return 失败返回
	 */
	public function getErrorTrend($where)
	{
		$sql = "select e.time,e.path_id,e.status,e.num,h.host,h.path  FROM `{$this->datawareStatusTable}` as e,`{$this->relationPathTable}` as h {$where} AND e.path_id=h.id ORDER BY `time` ASC";
		//echo $sql;exit;
		$ret = self::getIns()->fetchAll($sql, false, true);
		return $ret;
	}


	public function getRipTopTen($where)
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `ip`,`num`,`path`,`host` FROM `{$this->datawareIpTable}` inner join {$this->relationPathTable} where {$this->datawareIpTable}.path_id = {$this->relationPathTable}.id {$where} ORDER BY `num` DESC limit 10";
//        echo $sql;exit;
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function rtcTopTen($where)
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `ip`, `total_request_time`,`path`,`host` FROM `{$this->datawareIpTable}` inner join {$this->relationPathTable} where {$this->datawareIpTable}.path_id = {$this->relationPathTable}.id {$where} ORDER BY `total_request_time` DESC limit 10";
		//echo $sql;exit;
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function errorTopTen($where)
	{
		$sql    = "SELECT `time`, `host`, `path`,`status`,sum(num) AS num FROM `{$this->datawareStatusTable}` inner join  `{$this->relationPathTable}`  WHERE  `{$this->relationPathTable}`.id = `{$this->datawareStatusTable}`.path_id {$where} GROUP BY `path`	,`status` ORDER BY `num` DESC LIMIT 10";
//		echo $sql;exit;
		$result = self::getIns()->fetchAll($sql, false, true);
//		print_r($result);
//		exit;
		return $result;
	}

	public function mapStatistics($where)
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num`,sum(`total_request_time`) as `total_request_time`,max(`max_request_time`) as `max_request_time` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code`";
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function mapRequestTime($where)
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num`,sum(`total_request_time`) as `total_request_time`,max(`max_request_time`) as `max_request_time` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code`";
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function mapStatisticsTopTen($where)
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `country_code`,`country_name`,sum(`num`) as `num` FROM `{$this->datawareCountryTable}` {$where} GROUP BY `country_code` ORDER BY `num` DESC LIMIT 10";
//        echo $sql;exit;
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function ip2location($ip)
	{
		$dao    = new Ym_Dao('ip2location');
		$where  = "WHERE `ip_from` < $ip ORDER BY ip_from DESC LIMIT 1";
		$sql    = "SELECT `city_name` FROM `ip2location_db24` " . $where;
		$result = $dao->queryScalar($sql);
        $dao->selectDb('default');
		return $result;
	}

	public function globalCountry()
	{
//		$dao    = new Ym_Dao('default');
		$sql    = "SELECT `code`,`name` FROM `$this->relationCountryTable` ";
//        print_r($sql);
//        exit;
		$result = self::getIns()->fetchAll($sql, false, true);
		return $result;
	}

	public function getBusinessByUser($uid) {
		$sql = sprintf("SELECT GROUP_CONCAT(`name`) as `business` FROM `%s` WHERE `id` IN(SELECT `bid` FROM `%s` WHERE `uid` = %d)", $this->relationBusinessTable, $this->userBusinessTable, $uid);

		$result = self::getIns()->queryRow($sql, true);
		return $result;
	}

	/**
	 * 选择要查看的业务
	 */
	public function getBusiness()
	{
        $host_id = '';

		$loginUser = User_Session::getLoginCookie();
		$business = $this->getBusinessByUser($loginUser['id']);
		$hosts = $business['business'];

		if(!empty($hosts)) {
			$hosts = substr($hosts, 0, strlen($hosts) - 1);

            $sql = "select id from `{$this->relationPathTable}` where host in ($hosts)";

            $hosts_ids = self::getIns()->fetchAll($sql, false, true);

            foreach($hosts_ids AS $value){
                $host_id .= $value['id'] .",";
            }
            $host_id = rtrim($host_id, ',');
		}

		return $host_id;
	}

}
