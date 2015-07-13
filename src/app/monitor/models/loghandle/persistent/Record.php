<?php
class Model_Loghandle_Persistent_Record
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    /**
     * 获取记录时间
     * @param $recordName
     * @param $stepTime
     * @return mixed
     */
    public function getRecordTime($recordName, $stepTime) {
        $recordTime = 0;

        try {
            $this->dao->pdo->beginTransaction();

            $columns = array('time');
            $where   = sprintf("WHERE `name` = '%s'", $recordName);

            $result = $this->dao->get(Sys_Database::getTable('extract_record'), $columns, $where);

            $recordTime = $result['time'];

            // 如果记录时间不存在，则写入数据源最后一条记录的时间 - 步长时间
            if(empty($recordTime)) {
                $recordTime = time();
                $recordTime = $recordTime - $stepTime;

                $data = array('name' => $recordName, 'time' => $recordTime);

                $this->dao->insert(Sys_Database::getTable('extract_record'), $data);
            }

            $this->dao->pdo->commit();
        } catch(PDOException $e) {
            $this->dao->pdo->rollBack();
        }

        return $recordTime;
    }

    /**
     * 删除记录时间
     * @param $recordName
     * @return int
     */
    public function removeRecordTime($recordName) {
        $where = array( 'name' => $recordName);

        $result = $this->dao->delete(Sys_Database::getTable('extract_record'), $where);

        return $result;
    }


    /**
     * 更新记录时间
     * @param $recordName
     * @param $time
     * @return int
     */
    public function updateRecordTime($recordName, $time) {
        $data  = array('time' => $time);
        $where = array('name' => $recordName);

        $result = $this->dao->update(Sys_Database::getTable('extract_record'), $data, $where);

        return $result;
    }

} 