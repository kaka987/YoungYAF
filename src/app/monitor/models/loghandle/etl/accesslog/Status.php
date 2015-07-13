<?php

class Model_Loghandle_Etl_Accesslog_Status extends Model_Loghandle_Etl_Accesslog_Base
{
    public $datawareStatusTable;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareStatusTable = Sys_Database::getTable('dataware_status');
    }

    public function transform($data) {
        $returns = array();

        // 转制数据
        foreach ($data AS $v) {
            // 初始化数据
            if(!isset($returns[$v['server_id']][$v['path_id']][$v['status']])) {
                $returns[$v['server_id']][$v['path_id']][$v['status']]['host_id'] = $v['host_id'];
                $returns[$v['server_id']][$v['path_id']][$v['status']]['num'] = 0;
            }

            // 记录该访问的数据
            $returns[$v['server_id']][$v['path_id']][$v['status']]['num']++;
        }

        return $returns;
    }

    public function load($data, $time) {
        $time      = strtotime(date('Y-m-d H:i:00', $time));
        $insertNum = $updateNum = 0;

        $insertSql = sprintf("INSERT INTO `%s` (`time`, `host_id`, `path_id`, `num`, `status`,
         `server_id`) VALUES ", $this->datawareStatusTable);

        $updateForInsertSql = "INSERT INTO `tmp` (`time`, `path_id`, `status`, `num`) VALUES ";

        // 更新业务数据
        foreach ($data AS $serverId => $serverData) {
            foreach ($serverData AS $pathId => $pathData) {
                foreach ($pathData AS $status => $statusData) {
                    $where = sprintf("WHERE `time` = %d AND `path_id` = %d AND `status` = %d", $time, $pathId, $status);

                    $historyData = $this->dao->count($this->datawareStatusTable, $where);

                    if ($historyData) {
                        $updateForInsertSql .= sprintf("(%d, %d, %d, %d),",$time, $pathId, $status, $statusData['num']);
                        $updateNum ++;
                    } else {
                        $insertSql .= sprintf("(%d, %d, %d, %d, %d, %d),", $time, $statusData['host_id'], $pathId,
                            $statusData['num'], $status, $serverId);
                        $insertNum ++;
                    }
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
            $this->dao->exec("CREATE TEMPORARY TABLE tmp(`time` int(11), `path_id` int(11), `status` int(5), `num` int(11))");
            $this->dao->exec($updateForInsertSql);

            $sql = sprintf("UPDATE `%s` AS status, tmp SET `status`.`num` = `status`.`num` + `tmp`.`num`
              WHERE `status`.`time` = `tmp`.`time` AND `status`.`path_id` = `tmp`.`path_id`
              AND `status`.status = `tmp`.`status`", $this->datawareStatusTable);
            $this->dao->exec($sql);
        }
    }
} 