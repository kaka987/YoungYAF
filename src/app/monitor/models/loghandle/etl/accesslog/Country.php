<?php

class Model_Loghandle_Etl_Accesslog_Country extends Model_Loghandle_Etl_Accesslog_Base
{
    public $datawareCountryTable;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareCountryTable = Sys_Database::getTable('dataware_country');
    }

    public function transform($data) {
        $returns = array();

        // 转制数据
        foreach ($data AS $v) {
            $v['request_time']  = isset($v['request_time']) ? floatval($v['request_time']) : 0;
            $country = $this->getCountry(ip2long($v['remote']));

            // 初始化数据
            if(!isset($returns[$v['path_id']][$country['code']])) {
                $returns[$v['path_id']][$country['code']] = array(
                    'num'                 => 0,
                    'total_request_time'  => 0,
                    'max_request_time'    => array(),
                    'country_name'        => $country['name'],
                    'host_id'             => $v['host_id']
                );
            }

            // 记录该访问的数据
            $returns[$v['path_id']][$country['code']]['num']++;
            $returns[$v['path_id']][$country['code']]['total_request_time'] += $v['request_time'];
            $returns[$v['path_id']][$country['code']]['max_request_time'][] = $v['request_time'];
        }

        return $returns;
    }

    public function load($data, $time) {
        $time = strtotime(date('Y-m-d', $time));

        // 更新业务数据
        foreach ($data AS $pathId => $pathData) {
            foreach ($pathData AS $countryCode => $countryData) {
                // 获取最大请求时间
                $maxRequestTime = max($countryData['max_request_time']);

                $where   = sprintf("WHERE `time` = %d AND `path_id` = %d AND `country_code` = '%s'", $time, $pathId, $countryCode);
                $columns = array('max_request_time');

                $historyData = $this->dao->get($this->datawareCountryTable, $columns, $where);

                // 重新计算最大请求时间
                $maxRequestTime = max($maxRequestTime, $historyData['max_request_time']);

                if ($historyData) {
                    $data = array(
                        'num[+]'                => $countryData['num'],
                        'total_request_time[+]' => $countryData['total_request_time'],
                        'max_request_time'      => $maxRequestTime
                    );

                    $this->dao->update($this->datawareCountryTable, $data, $where);
                } else {
                    $data = array(
                        'time'               => $time,
                        'host_id'            => $countryData['host_id'],
                        'path_id'            => $pathId,
                        'country_code'       => $countryCode,
                        'country_name'       => $countryData['country_name'],
                        'num'                => $countryData['num'],
                        'total_request_time' => $countryData['total_request_time'],
                        'max_request_time'   => $maxRequestTime
                    );

                    $this->dao->insert($this->datawareCountryTable, $data);
                }
            }
        }
    }

    /**
     * 通过传入整型的ip地址查出所在国家码
     * @param int $ip
     * @return 失败返回
     */
    public function getCountry($ip) {
        $dao = new Dao_Medoo(null, 'ip2location');

        $columns = array(
            'country_code(code)',
            'country_name(name)'
        );

        $where = array(
            'ip_from[<]' => $ip,
            'ORDER'      => 'ip_from DESC'
        );

        $result = $dao->get('ip2location_db24', $columns, $where);

        return $result;
    }
} 