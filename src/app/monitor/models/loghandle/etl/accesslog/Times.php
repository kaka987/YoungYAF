<?php

class Model_Loghandle_Etl_Accesslog_Times extends Model_Loghandle_Etl_Accesslog_Base
{
    public $datawareTimesTable = 'dataware_times';

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareTimesTable = Sys_Database::getTable('dataware_times');
    }

    public function transform($data) {
        $returns = array();

        // 转制数据
        foreach ($data AS $v) {
            $v['request_time'] = isset($v['request_time']) ? floatval($v['request_time']) : 0;

            // 初始化数据
            if(!isset($returns[$v['path_id']])) {
                $returns[$v['path_id']] = array(
                    'num'                 => 0,
                    'total_response_time' => 0,
                    'max_response_time'   => array(),
                    'host_id'             => $v['host_id']
                );
            }

            // 记录该访问的数据
            $returns[$v['path_id']]['num']++;
            $returns[$v['path_id']]['total_response_time'] += $v['request_time'];
            $returns[$v['path_id']]['max_response_time'][] = $v['request_time'];

        }

        return $returns;
    }

    public function load($data, $time) {
        $time      = strtotime(date('Y-m-d H:i:00', $time));
        $insertNum = $updateNum = 0;

        $insertSql = sprintf("INSERT INTO `%s` (`time`, `host_id`, `path_id`, `num`, `total_response_time`,
         `max_response_time`) VALUES ", $this->datawareTimesTable);

        $updateForInsertSql = "INSERT INTO `tmp` (`time`, `path_id`, `num`, `total_response_time`,
         `max_response_time`) VALUES ";

        // 更新业务数据
        foreach ($data AS $pathId => $pathData) {
            // 获取最大响应时间
            $maxResponseTime = max($pathData['max_response_time']);

            $where   = sprintf("WHERE `time` = %d AND `path_id` = %d", $time, $pathId);
            $columns = array('max_response_time');

            $historyData = $this->dao->get($this->datawareTimesTable, $columns, $where);

            // 重新计算最大响应时间
            $maxResponseTime = max($maxResponseTime, $historyData['max_response_time']);

            if ($historyData) {
                $updateForInsertSql .= sprintf("(%d, %d, %d, %f, %f),",
                    $time, $pathId, $pathData['num'], $pathData['total_response_time'], $maxResponseTime);
                $updateNum ++;
            } else {
                $insertSql .= sprintf("(%d, %d, %d, %d, %f, %f),",
                    $time, $pathData['host_id'], $pathId, $pathData['num'], $pathData['total_response_time'], $maxResponseTime);
                $insertNum ++;
            }
        }

        $insertSql          = $insertNum > 0 ? substr($insertSql, 0, strlen($insertSql) - 1) : "";
        $updateForInsertSql = $updateNum > 0 ? substr($updateForInsertSql, 0, strlen($updateForInsertSql) - 1) : "";

        if ($insertNum) {
            $this->dao->exec($insertSql);
        }

        if ($updateNum) {
            // 更新用的临时表
            $this->dao->exec("CREATE TEMPORARY TABLE tmp(`time` int(11), `path_id` int(11),
            `num` int(11), `total_response_time` float(7), `max_response_time` float(7))");

            $this->dao->exec($updateForInsertSql);

            $sql = sprintf("UPDATE `%s` AS times, tmp SET `times`.`num` = `times`.`num` + `tmp`.`num`,
             `times`.`total_response_time` = `times`.`total_response_time` + `tmp`.`total_response_time`,
             `times`.`max_response_time` = `tmp`.`max_response_time` WHERE `times`.`time` = `tmp`.`time`
              AND `times`.`path_id` = `tmp`.`path_id`", $this->datawareTimesTable);

            $this->dao->exec($sql);
        }
    }
} 