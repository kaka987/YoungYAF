<?php

class Model_Loghandle_Etl_Accesslog_Size extends Model_Loghandle_Etl_Accesslog_Base
{
    public $datawareSizeTable;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareSizeTable = Sys_Database::getTable('dataware_size');
    }

    public function transform($data) {
        $returns = array();

        // 转制数据
        foreach ($data AS $v) {
            // 初始化数据
            if(!isset($returns[$v['path_id']])) {
                $returns[$v['path_id']]['host_id'] = $v['host_id'];
                $returns[$v['path_id']]['size'] = 0;
            }

            // 记录该路径的数据大小
            $returns[$v['path_id']]['size'] += $v['size'];
        }

        return $returns;
    }


    public function load($data, $time) {
        $time      = strtotime(date('Y-m-d', $time));
        $insertNum = $updateNum = 0;

        $insertSql = sprintf("INSERT INTO `%s` (`time`, `host_id`, `path_id`, `size`) VALUES ", $this->datawareSizeTable);

        $updateForInsertSql = "INSERT INTO `tmp` (`time`, `path_id`, `size`) VALUES ";

        // 更新业务数据
        foreach ($data AS $pathId => $pathData) {
            $where = sprintf("WHERE `time` = %d AND `path_id` = %d", $time, $pathId);

            $historyData = $this->dao->count($this->datawareSizeTable, $where);

            if ($historyData) {
                $updateForInsertSql .= sprintf("(%d, %d, %d),", $time, $pathId, $pathData['size']);
                $updateNum ++;
            } else {
                $insertSql .= sprintf("(%d, %d, %d, %d),", $time, $pathData['host_id'], $pathId, $pathData['size']);
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
            $this->dao->exec("CREATE TEMPORARY TABLE tmp(`time` int(11), `path_id` int(11), `size` int(11))");

            $this->dao->exec($updateForInsertSql);

            $sql = sprintf("UPDATE `%s` AS `size`, tmp SET `size`.`size` = `size`.`size` + `tmp`.`size`
              WHERE `size`.`time` = `tmp`.`time` AND `size`.`path_id` = `tmp`.`path_id`", $this->datawareSizeTable);

            $this->dao->exec($sql);
        }
    }
} 