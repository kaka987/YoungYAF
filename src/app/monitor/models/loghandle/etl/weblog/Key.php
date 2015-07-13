<?php

class Model_Loghandle_Etl_Weblog_Key extends Model_Loghandle_Etl_Weblog_Base
{
    public $datawareKeyTable;

    public function __construct($logType) {
        parent::__construct($logType);

        $this->datawareKeyTable = Sys_Database::getTable('dataware_key');
    }

    public function transform($data) {
        $returns = array();

        $logApp = $data['logapp'];

        $keys = array();

        foreach ($logApp[$data['table']] AS $appId => $param) {
            $param = str_replace("\r\n", '|', $param);
            $param = str_replace("\n", '|', $param);

            $params = explode('|', $param);

            $temp = array();

            foreach ($params AS $key) {
                $key = trim(substr($key, 0 , stripos($key, '=')));

                $temp[] = $key;
            }
            $keys[$appId] = $temp;
        }

        foreach ($data['data'] AS $v) {
            $appId = $v['logid'];
            $key = isset($keys[$appId]) ? $keys[$appId] : array();

            foreach ($key AS $val) {
                $msg = stripos($v['message'], $val);
                if ($msg > 0 || $msg === 0) {
                    // 初始化数据
                    if(!isset($returns[$appId][$val])) {
                        $returns[$appId][$val] = array(
                            'num'        => 0,
                            'content_id' => $v['_id']->{'$id'},
                            'sample'     => $v['message']
                        );
                    }
                    $returns[$appId][$val]['num']++;
                }
            }
        }

        return $returns;
    }

    public function load($data, $time) {
        $time = strtotime(date('Y-m-d H:i:00', $time));
        $insertNum = $updateNum = 0;

        $insertSql = sprintf("INSERT INTO `%s` (`time`, `log_app_id`, `key`, `num`, `content_id`,
         `sample`) VALUES ", $this->datawareKeyTable);

        $updateForInsertSql = "INSERT INTO `tmp` (`time`, `log_app_id`, `key`, `num`) VALUES ";

        // 更新业务数据
        foreach ($data AS $appId => $appData) {
            foreach($appData AS $key => $content) {
                $where = sprintf("WHERE `time` = %d AND `log_app_id` = %d AND `key` = '%s'", $time, $appId, $key);

                $historyData = $this->dao->count($this->datawareKeyTable, $where);

                if ($historyData) {
                    $updateForInsertSql .= sprintf("(%d, %d, '%s', %d),", $time, $appId, $key, $content['num']);
                    $updateNum ++;
                } else {
                    $insertSql .= sprintf("(%d, %d, '%s', %d, '%s', '%s'),", $time, $appId, $key, $content['num'],
                        $content['content_id'], $content['sample']);
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
            $this->dao->exec("CREATE TEMPORARY TABLE tmp(`time` int(11), `log_app_id` int(11),
            `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci, `num` int(11))");

            $this->dao->exec($updateForInsertSql);

            $sql = sprintf("UPDATE `%s` AS `key`, tmp SET `key`.`num` = `key`.`num` + `tmp`.`num`
              WHERE `key`.`time` = `tmp`.`time` AND `key`.`log_app_id` = `tmp`.`log_app_id`
              AND `key`.`key` = `tmp`.`key`", $this->datawareKeyTable);

            $this->dao->exec($sql);
        }
    }
}