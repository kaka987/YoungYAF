<?php
/**
 * 报警配置
 *
 * @author      Zhangy<young@yeahmobi.com>
 * @package     models
 * @version     Version 1.0
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Model_Alarm_Config
{
	/**
	 * 模型表
	 */
	public $monitorAlarmConfigTable;
	public $monitorLogConfigTable;

	/**
	 * 访问初始化
	 */
	public function __construct()
	{
		$this->dao = new Ym_Dao('log');

        $this->monitorAlarmConfigTable 	= Sys_Database::getTable('monitor_alarmconfig');
        $this->monitorLogConfigTable   	= Sys_Database::getTable('monitor_logconfig');
        $this->monitorRelationHostTable = Sys_Database::getTable('relation_host');
        $this->monitorRelationServerTable = Sys_Database::getTable('relation_server');
        $this->monitorRelationUGTable = Sys_Database::getTable('relation_user_group');
        $this->monitorUserGroupsTable   = Sys_Database::getTable('user_groups');
        $this->monitorUserTable   		= Sys_Database::getTable('user');

        $this->monitorApps              = Sys_Database::getTable('apps');
        $this->monitorHosts             = Sys_Database::getTable('hosts');
        $this->monitorServices          = Sys_Database::getTable('services');

        $this->workerTable              = Sys_Database::getTable('worker');
        $this->triggerTable             = Sys_Database::getTable('trigger');
        $this->monitorRelationLATable   = Sys_Database::getTable('relation_log_action');
        $this->actionsTable             = Sys_Database::getTable('monitor_actions');

	}
	
	public function insertData(array $data = array(), $table='') {

        if (empty($table)) $table = $this->monitorAlarmConfigTable;
		return $this->dao->insert($table, $data);
	}
	
	public function updateData($data, $condition, $table='') {
		
		if (empty($table)) $table = $this->monitorAlarmConfigTable;
		
		return $this->dao->update($table, $data, $condition);
	}
	
	public function deleteData($id=NULL, $table='') {
		
		if (empty($table)) $table = $this->monitorAlarmConfigTable;
		
		if ($id !== NULL) {
			$where = ' id='.$id.' limit 1';
			return $this->dao->delete($table, $where);
		}

		return FALSE;
	}
	
	public function selectData($id=NULL, $start=0, $limit=10, $table='') {
		
		if (empty($table)) $table = $this->monitorAlarmConfigTable;
		
		$sql = 'select * from '.$table;
		
		$where = ' where `id`='.$id.' limit 1';
		
		if ($id === NULL) {
			$where = ' order by `id` limit '.$start.','.$limit;
		}
		
		if ($start==-1) {
			$where = '';
			$sql = "select count(*) as num from ".$table;
			return $this->dao->queryRow($sql, TRUE);
		}
		
		$sql .= $where;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getlogConfig() {
		
		$sql = "select id,monitor_app from ".$this->monitorLogConfigTable;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getMonitorConfig() {
	
		$where = " where monitor_status=1";
		$sql = "select monitor_service,monitor_url,monitor_param from ".$this->monitorAlarmConfigTable.$where;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getMonitorApp() {
		
		$sql = "select distinct app as monitor_app from ".$this->monitorApps;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getMonitorUrl() {
		
		$sql = "select host from ".$this->monitorHosts;
		return $this->dao->fetchAll($sql,'', true);
	}

    public function getMonitorService() {

        $sql = "select service from ".$this->monitorServices;
        return $this->dao->fetchAll($sql,'', true);
    }
	
	public function getMonitorGroup() {
		
		$sql = "select id,groupname from ".$this->monitorUserGroupsTable;
		return $this->dao->fetchAll($sql,'', true);
	}
	
	public function getMonitorUser() {
		
		$sql = "select id,aliasname,fullname from ".$this->monitorUserTable;
		return $this->dao->fetchAll($sql,'', true);
	}	
	
	public function selectFromUser() {}
	
	public function selectFromGroup() {}
	
	public function insertUserGroup($uid,$group_ids) {
		
		$str = '';
		foreach ($group_ids as $gid) {
			$str .= "('',".$uid.",".$gid."),";
		}
		$sql = "insert into ".$this->monitorRelationUGTable. "(id,user_id,group_id) values".rtrim($str,',');
		return $this->dao->query($sql);
	}
	
	public function updateUserGroup($uid,$group_ids) {
		
		$this->deleteUserGroup($uid);
		return $this->insertUserGroup($uid, $group_ids);
	}
	
	public function deleteWorker($uid) {
		
		$where = ' workerid='.$uid;
		return $this->dao->delete($this->monitorRelationLATable, $where);
	}

    public function deleteAction($uid) {

        $where = ' actionid='.$uid;
        return $this->dao->delete($this->monitorRelationLATable, $where);
    }

	public function insertGroupUser($gid,$user_ids) {
		
		$str = '';
		foreach ($user_ids as $uid) {
			$str .= "('',".$uid.",".$gid."),";
		}
		$sql = "insert into ".$this->monitorRelationUGTable. "(id,user_id,group_id) values".rtrim($str,',');
		return $this->dao->query($sql);
	}
	
	public function updateGroupUser($gid,$user_ids) {
		
		$this->deleteGroupUser($gid);
		return $this->insertGroupUser($gid,$user_ids);
	}
	
	public function deleteGroupUser($gid) {
		
		$where = ' group_id='.$gid;
		return $this->dao->delete($this->monitorRelationUGTable, $where);
	}
	
	public function getUGIds($uid=0,$gid=0) {
		
		if ($uid) { 
			$key1 = "group_id";
			$key2 = "user_id";
			$key3 = "uid";
		}
			
		if ($gid) {
			$key1 = "user_id";
			$key2 = "group_id";
			$key3 = "gid";
		}
		
		$sql = "select ".$key1." from ".$this->monitorRelationUGTable. " where ".$key2."=".$$key3;
		
		$r = $this->dao->fetchAll($sql,'', true);
		$str = '';
		if ($r !== FALSE) {
			foreach ($r as $v) {
				
				$str .= $v[$key1].',';
			}
			return rtrim($str,',');
		}
		return $r;
	}

    public function insertWorker($data) {

        return $this->dao->insert($this->workerTable, $data);
    }

    public function insertRLA($logid,$workers,$actions) {

        $sql = "select rlaid from ".$this->monitorRelationLATable."
                where logid=".$logid." and workerid=".$workers." and actionid=".$actions." limit 1";
        if (!$this->dao->queryRow($sql, true)) {
            $data = array('logid'=>$logid,'workerid'=>$workers,'actionid'=>$actions);
            return $this->dao->insert($this->monitorRelationLATable,$data);
        }
        return FALSE;
    }

    public function deleteRLA($actionId=0) {

        $where = ' `actionid`='.$actionId;
        return $this->dao->delete($this->monitorRelationLATable, $where);
    }

    public function insertTriggers($config) {

        return $this->dao->insert($this->triggerTable,$config);
    }

    public function getNames($rlaid=0) {

        $sql = "select l.logid,l.service,w.id as workerid,w.name as workername,a.id as actionid,a.name as actionname from
                ".$this->monitorLogConfigTable." as l,
                ".$this->workerTable." as w,
                ".$this->actionsTable." as a,
                ".$this->monitorRelationLATable." as r
                 where r.logid=l.logid and r.workerid=w.id and r.actionid=a.id
                 and r.rlaid=".$rlaid." limit 1";

        echo $sql;

        return $this->dao->queryRow($sql, TRUE);
    }

    public function getApps($actionId=0) {

        $sql = "select * from {$this->monitorRelationLATable} where actionid=".$actionId;

        return $this->dao->fetchAll($sql,'', true);
    }

    public function getWorkers(){

        $sql = "select id,name from ".$this->workerTable;
        return $this->dao->fetchAll($sql,'', true);
    }

    public function getLogs(){

        $sql = "select distinct service from ".$this->monitorLogConfigTable;
        return $this->dao->fetchAll($sql,'', true);
    }

}