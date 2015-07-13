<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Log
{

	/**
	 * 模型表
	 */
	public $monitorLogConfigTable;
    public $monitorLogTypeTable;

	/**
	 * 访问初始化
	 */
	public function __construct()
	{
		$this->dao = new Ym_Dao('log');

        $this->monitorLogConfigTable = Sys_Database::getTable('monitor_logconfig');
        $this->monitorLogTypeTable   = Sys_Database::getTable('monitor_logtype');
        $this->monitorLogAction   = Sys_Database::getTable('relation_logconfig_actions');
        $this->monitorAction = Sys_Database::getTable('monitor_actions');
        $this->monitorWorker = Sys_Database::getTable('worker');

    }

    public function insertLogconfigActions($logconfigId,$log_worker_actions) {

        $sql = "insert into ".$this->monitorLogAction."(`logid`,`workerid`,`actionid`) values";
        foreach($log_worker_actions as $lwa) {

            $sql .= '('.$logconfigId.','.$lwa[0].','.$lwa[1].'),';
        }
        return $this->dao->query(rtrim($sql,','));
    }

    public function updateLogconfigActions($logconfigId, $log_worker_actions) {

        $r = FALSE;
        if ($logconfigId !== NULL) {
            $where = ' logid='.$logconfigId;
            $r = $this->dao->delete($this->monitorLogAction, $where);
        }

        if ($r) {

            return $this->insertLogconfigActions($logconfigId,$log_worker_actions);
        }

        return $r;
    }

	public function saveData(array $data = array()) {
		
		return $this->dao->insert($this->monitorLogConfigTable, $data);
	}

	public function saveType($data){
//        print_r($data);exit;
        return $this->dao->insert($this->monitorLogTypeTable, $data);
    }
	public function updateData($data, $condition) {
		
		return $this->dao->update($this->monitorLogConfigTable, $data, $condition);
	}

	public function deleteData($id=NULL) {
		
		if ($id !== NULL) {
			$where = ' logid='.$id.' limit 1';
			$r = $this->dao->delete($this->monitorLogConfigTable, $where);

            if ($r) {
                $where = ' logid='.$id;
                return $this->dao->delete($this->monitorLogAction, $where);
            }
            else return FALSE;
		}

		return FALSE;
	}
	
	public function selectData($id=NULL, $start=0, $limit=10) {
		
		$sql = "select * from {$this->monitorLogConfigTable}";
		
		$where = ' where logid='.$id.' limit 1';
		
		if ($id === NULL) {
			$where = ' order by logid limit '.$start.','.$limit;
		}
		
		if ($start==-1) {
			$where = '';
			$sql = "select count(*) as num from ".$this->monitorLogConfigTable;
			return $this->dao->queryRow($sql, TRUE);
		}
		
		$sql .= $where;
		return $this->dao->fetchAll($sql,'', true);
	}

	public function getType() {
        $sql = 'select * from '.$this->monitorLogTypeTable;
        return $this->dao->fetchAll($sql,'', true);
    }

	public function getMonitorConfig() {
	
		$where = " where monitor_status=1";
		$sql = "select monitor_service,monitor_url,monitor_param from ".$this->monitorLogConfigTable.$where;
		return $this->dao->fetchAll($sql,'', true);
	}
    #将表中的id自增从1开始
    public function query(){
        $sql = "alter table ".$this->monitorLogConfigTable." AUTO_INCREMENT=1";
        return $this->dao->query($sql);
    }

    public function getActions(){

        $sql = "select id,name from ".$this->monitorAction;
        return $this->dao->fetchAll($sql,'', true);
    }

    public function getWorkers(){

        $sql = "select id,name from ".$this->monitorWorker;
        return $this->dao->fetchAll($sql,'', true);
    }

    public function getLogs(){

        $sql = "select logid,service from ".$this->monitorLogConfigTable;
        return $this->dao->fetchAll($sql,'', true);
    }

    public function selectedWorkerAction($id=0) {

        $sql = "select logid,workerid,actionid from ".$this->monitorLogAction. " where logid=".$id;
        return $this->dao->fetchAll($sql,'', true);
    }

}