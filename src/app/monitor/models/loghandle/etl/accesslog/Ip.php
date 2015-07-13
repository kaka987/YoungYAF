<?php

class Model_Loghandle_Etl_Accesslog_Ip extends Model_Loghandle_Etl_Accesslog_Base
{
    public $datawareIpTable;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareIpTable = Sys_Database::getTable('dataware_ip');
    }

    public function transform($data) {
        $returns = array();

        // 转制数据
        foreach ($data AS $v) {
            $v['request_time'] = isset($v['request_time']) ? floatval($v['request_time']) : 0;
            $v['ip']           = ip2long($v['remote']);

            // 初始化数据
            if(!isset($returns[$v['path_id']][$v['ip']])) {
                $returns[$v['path_id']][$v['ip']] = array(
                    'num'                => 0,
                    'total_request_time' => 0,
                    'max_request_time'   => array(),
                    'host_id'            => $v['host_id']
                );
            }

            // 记录该访问的数据
            $returns[$v['path_id']][$v['ip']]['num']++;
            $returns[$v['path_id']][$v['ip']]['total_request_time'] += $v['request_time'];
            $returns[$v['path_id']][$v['ip']]['max_request_time'][] = $v['request_time'];
        }

        return $returns;
    }


    public function load($data, $time) {
        $time = strtotime(date('Y-m-d', $time));
        $insertNum = $updateNum = 0;

        $insertSql = sprintf("INSERT INTO `%s` (`time`, `host_id`, `path_id`, `ip` `num`,`total_request_time`,
         `max_request_time`) VALUES ", $this->datawareIpTable);

        $updateForInsertSql = "INSERT INTO `tmp` (`time`, `path_id`, `ip`, `num`, `total_request_time`,
         `max_request_time`) VALUES ";


        // 更新业务数据
        foreach ($data AS $pathId => $pathData) {
            foreach ($pathData AS $ip => $ipData) {
                // 获取最大请求时间
                $maxRequestTime = max($ipData['max_request_time']);

                $where   = sprintf("WHERE `time` = %d AND `path_id` = %d AND `ip` = %d", $time, $pathId, $ip);
                $columns = array('max_request_time');

                $historyData = $this->dao->get($this->datawareIpTable, $columns, $where);

                // 重新计算最大请求时间
                $maxRequestTime = max($maxRequestTime, $historyData['max_request_time']);

                if ($historyData) {
                    $updateForInsertSql .= sprintf("(%d, %d, %d, %d, %f, %f),", $time, $pathId, $ip,
                        $ipData['num'], $ipData['total_request_time'], $maxRequestTime);
                    $updateNum ++;
                } else {
                    $insertSql .= sprintf("(%d, %d, %d, %d, %d, %f, %f),", $time, $ipData['host_id'], $pathId, $ip,
                        $ipData['num'], $ipData['total_request_time'], $maxRequestTime);
                    $insertNum ++;
                }
            }
        }

        $insertSql          = $insertNum > 0 ? substr($insertSql, 0, strlen($insertSql) - 1) : "";
        $updateForInsertSql = $updateNum > 0 ? substr($updateForInsertSql, 0, strlen($updateForInsertSql) - 1) : "";

        if ($insertNum) {
            $this->dao->exec($insertSql);
        }

        if ($updateNum) {
            // 更新用的临时表
            $this->dao->exec("CREATE TEMPORARY TABLE tmp(`time` int(11), `path_id` int(11), `ip` int(11), `num` int(11), `total_request_time` float(7), max_request_time float(7))");
            $this->dao->exec($updateForInsertSql);

            $sql = sprintf("UPDATE `%s` AS `ip`, tmp SET `ip`.`num` = `ip`.`num` + `tmp`.`num`,
             `ip`.`total_response_time` = `ip`.`total_response_time` + `tmp`.`total_response_time`,
             `ip`.`max_response_time` = `tmp`.`max_response_time` WHERE `ip`.`time` = `tmp`.`time`
              AND `ip`.`path_id` = `tmp`.`path_id` AND `ip`.`ip` = `tmp`.`ip`", $this->datawareIpTable);
            $this->dao->exec($sql);
        }
    }
} 