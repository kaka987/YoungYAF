<?php
/**
 * Dao 操作类
 * 
 * @author 常博 <prince.chang@yeahmobi.com>
 * @version 1.0.0
 * @package ym
 * @category script
 * @copyright 2014-2015 Yeahmobi@inc
 */
 class Ym_Dao
 {
 	/*
 	 * 数据库环境 （默认，开发， 测试，线上）
    */
    public static $dbEnv = 'default';
    
    public function __construct($dbEnv='')
 	{
 		if($dbEnv) {
 			self::$dbEnv  = $dbEnv;
 		}
 	}
 	
 	public function query($sql, $dbType='write') {
 		$db = new Ym_DB(self::$dbEnv,  $dbType);
 		$result = $db->query($sql);
 		return $result;
 	}
 	
 	public function insert($table, $data, $fields = null) {
 		$db = new Ym_DB(self::$dbEnv, 'write');
 		$result = $db->insert($table, $data, $fields);
 		return $result;
 	}
 	
 	public function update($table, $data, $where) {
 		$db = new Ym_DB(self::$dbEnv, 'write');
 		$result = $db->update($table, $data, $where);
 		return $result;
 	}
 	
 	public function delete($table, $where) {
 		$db = new Ym_DB(self::$dbEnv, 'write');
 		$sql = ' DELETE FROM `'.$table.'` WHERE '.$where;
 		$result = $db->query($sql);
 		return $result;
 	}
 	
 	/**
     * *查询指定SQl 所有记录
     *
     * @param  <string> $sql SQL查询语句
     * @param  <string> $keyField 指定记录结果键值使用哪个字段,默认为 false 使用 regI{0...count}
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象;默认 false
     * @return 失败返回 false
     */
 	public function fetchAll($sql, $keyField = false, $assoc = false) {
 		$db = new Ym_DB(self::$dbEnv, 'read');
        $result = $db->queryAll($sql, $keyField, $assoc);
 		return $result;
 	}
 	
 	    /**
     * *查询指定SQl 第一行，第一列 值
     *
     * @param  <string> $sql SQL查询语句
     * @return 失败返回 false
     */
    public function queryScalar($sql) {
    	$db = new Ym_DB(self::$dbEnv, 'read');
 		$result = $db->queryScalar($sql);
 		return $result;
    }
    
    /**
     * *查询指定SQl 第一行记录
     *
     * @param  <string> $sql SQL查询语句
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象; 默认 false
     * @return 失败返回 false
     */
    public function queryRow($sql, $assoc = false) {
    	$db = new Ym_DB(self::$dbEnv, 'read');
 		$result = $db->queryRow($sql,$assoc);
 		return $result;
    }

     /**
      * 关闭自动提交
      */
    public function transactionBegin()
    {
        $db = new Ym_DB(self::$dbEnv, 'write');
        $db->commitBegin();
    }

    /**
    * 提交事务
    */
    public function transactionCommit()
    {
        $db = new Ym_DB(self::$dbEnv, 'write');
        $db->commitEnd();
    }

    public function setError()
    {
        Ym_DB::$isError = true;
    }

    public function selectDb($db)
    {
        self::$dbEnv = $db;
    }

     public function isError() {
         return Ym_DB::$isError;
     }
 }
?>
