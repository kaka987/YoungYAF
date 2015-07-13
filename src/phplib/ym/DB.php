<?php

/**
 * mysqli 操作类
 *
 * @author 常博 <prince.chang@yeahmobi.com>
 * @version 1.0.0
 * @package ym
 * @category script
 * @copyright 2014-2015 Yeahmobi@inc
 */
class Ym_DB
{
    /**
     * *错误标示
     */
    public static $isError = false;

    /**
     * *当执行出错时是否中断退出
     */
    public static $isErrorExit = false;

    /**
     * *mysqli 实例集合
     */

    protected static $mysqliInstances = array();

    /**
     * *当前结果集
     */
    protected static $result = false;

    /**
     * 当前实例mysql配置
     * */
    protected static $dbConfig = array();

    /**
     * 数据库名称，默认为default
     */
    protected static $dbEnv = 'default';

    /**
     * 数据库读写类型
     *
     * */
    protected static $dbType = 'read';

    /**
     * 当mysql 链接失败时，重连次数 relinkcounty
     * */
    protected static $reLinkCounty = 3;

    /**
     * 是否需要返回insertid
     * */
    protected static $isInsertId = false; //在执行insert 操作后，是否需要返回insert_id

    /**
     * 初始化当前数据库配置
     * @param <string> $dbType  数据库环境（默认, 开发, 测试, 生产）
     *
     */
    public function __construct($dbEnv = '', $dbType = '')
    {
        /**
         * 获取当前数据库配置
         */
        if ($dbEnv) {
            self::$dbEnv = $dbEnv;
        }
        if ($dbType) {
            self::$dbType = $dbType;
        }
        self::$dbConfig = self::getDbConfig(self::$dbEnv, self::$dbType);
    }

    /**
     * 获取数据库配置
     * @param <string> $dbEnv 数据库环境
     * @param <string> $dbType 数据库读写类型
     * @return <array> $dbConfig 数据库配置数组，包括：host, username, password, dbname , 可选（dbcharset）
     * */
    public static function getDbConfig($dbEnv, $dbType)
    {
        //$config = new Yaf_Config_Ini('./config.ini', 'staging');
        //self::$dbConfig = $config->database->get($dbType);
        //调张洋的接口，获取数据库配置
        //$dbConfig = Ym_getConfig($dbEnv, $dbType);
        //$filepath              = YPP_DIR_CONF . '/app/kangjian/config.ini';
        $filepath              = YPP_DIR_CONF . '/app/config.ini';
        $config                = new Yaf_Config_Ini($filepath);
        $dbConfig['host']      = $config->database->get("database")->$dbEnv->host;
        $dbConfig['username']  = $config->database->get("database")->$dbEnv->username;
        $dbConfig['password']  = $config->database->get("database")->$dbEnv->password;
        $dbConfig['dbname']    = $config->database->get("database")->$dbEnv->dbname;
        $dbConfig['dbcharset'] = $config->database->get("database")->$dbEnv->dbcharset;
        return $dbConfig;
    }

    /**
     * 链接 mysql
     * @param <array> $dbConfig 数据库配置数组，包括：host, username, password, dbname , 可选（dbcharset）
     * @return <object> $mysqli 数据库链接
     */
    private static function connect($dbConfig)
    {
        try {
            $mysqli = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['dbname']);
        } catch (mysqli_sql_exception $e) {
            $error = "Database Connect failed, Error Code: " . $e->getCode() . ' ,Error Message: ' . $e->getMessage();
            echo $error;
            self::log($error);
            return false;
        }
        $dbcharset = $dbConfig['dbcharset'] ? $dbConfig['dbcharset'] : 'utf8';
        $mysqli->set_charset($dbcharset);
        return $mysqli;
    }

    /**
     *获取mysql 连接实例
     *
     * @param <string> $dbEnv 数据库环境：默认，开发， 测试，线上；
     * @param <string> $dbType 数据库读写类型（读写分离）： 读 read， 写：write
     * @return <objact> $resource 数据库连接
     **/
    public static function getDbConnect($dbEnv = 'default', $dbType = 'read')
    {
        if (!isset(self::$mysqliInstances[$dbEnv][$dbType]) || !self::isActive($dbEnv, $dbType, false)) {
            $conf = self::getDbConfig($dbEnv, $dbType);
            if ($conf) {
                self::$mysqliInstances[$dbEnv][$dbType]=  self::connect($conf);
            } else {
                $error = " DB config not exist dbEnv:" . $dbEnv . ' dbType:' . $dbType;
                echo $error;
                self::log($error);
                return false;
            }
        }

        return self::$mysqliInstances[$dbEnv][$dbType];
    }

    /**
     * 检查当前mysql 链接是否有限
     * @param <string> $dbEnv 数据库环境：默认，开发， 测试，线上；
     * @param <string> $dbType 数据库读写类型（读写分离）： 读 read， 写：write
     * @param <bool> $retry 如果当前链接无效，是否需要重新链接
     * @return <bool> true or false
     * */
    public static function isActive($dbEnv, $dbType, $retry = false)
    {
        $mysqli = self::$mysqliInstances[$dbEnv][$dbType];
        if (!$mysqli) {
            return false;
        } else {
            if (mysqli_ping($mysqli)) {
                return true;
            } else {
                if ($retry) {
                    $mysqli = self::reLink($dbEnv, $dbType);
                    if ($mysqli) {
                        return true;
                    } else {
                        return false;
                    }
                }
                return false;
            }
        }
    }

    /**
     * 重新链接mysql
     * @param <string> $dbEnv 数据库环境：默认，开发， 测试，线上；
     * @param <string> $dbType 数据库读写类型（读写分离）： 读 read， 写：write
     * @return <object/bool> $mysqli 链接成功，返回mysql 链接，失败返回false
     * */
    public static function reLink($dbEnv, $dbType)
    {
        $mysqli = null;

        $conf   = self::getDbConfig($dbEnv, $dbType);
        $count  = self::$reLinkCounty;
        while ($count > 0 && !$mysqli) {
            $mysqli = self::getDbConnect($dbEnv, $dbType);
            $count--;
        }
        return $mysqli;
    }

    /**
     * *执行查询
     *
     * @param  <string> $sql SQL查询语句
     * @return 成功赋值并返回self::$result; 失败返回 false 如果有事务则回滚
     */
    public static function query($sql)
    {
        $mysqli = self::getDbConnect(self::$dbEnv, self::$dbType);
        $result = $mysqli->query($sql);
        if ($mysqli->error) {
            $error         = "SQL Query Error: " . $mysqli->error;
            self::$isError = true;
            self::log($error);
            if (self::$isErrorExit) exit;
            return false;
        }
        //是否需要在执行insert 操作后返回 insert id
        if (self::$isInsertId) {
            $insertid         = self::insertId($mysqli);
            self::$isInsertId = false;
            if ($insertid) {
                return $insertid;
            }
        }
        return $result;
    }

    /**
     * *查询指定SQl 第一行，第一列 值
     *
     * @param  <string> $sql SQL查询语句
     * @return 失败返回 false
     */
    public static function queryScalar($sql)
    {
        $result = self::query($sql);
        if ($result) {
            return self::fetchScalar($result);
        }
        return false;
    }

    /**
     * *查询指定SQl 第一行记录
     *
     * @param  <string> $sql SQL查询语句
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象; 默认 false
     * @return 失败返回 false
     */
    public static function queryRow($sql, $assoc = false)
    {
        $result = self::query($sql);
        if ($result) {
            return self::fetchRow($result, $assoc);
        } else {
            return false;
        }
    }

    /**
     * *查询指定SQl 所有记录
     *
     * @param  <string> $sql SQL查询语句
     * @param  <string> $keyField 指定记录结果键值使用哪个字段,默认为 false 使用 regI{0...count}
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象;默认 false
     * @return 失败返回 false
     */
    public static function queryAll($sql, $keyField = false, $assoc = false)
    {
        $result = self::query($sql);
        if ($result) {
            return self::fetchAll($result, $keyField, $assoc);
        } else {
            return false;
        }
    }

    /**
     * *取结果(self::$result)中第一行，第一列值
     * @param <object> $result :查询结果数据集
     * @return 没有结果返回 false
     */
    public static function fetchScalar($result)
    {
        if (!empty($result)) {
            $row = $result->fetch_array();
            return $row[0];
        } else {
            return false;
        }
    }

    /**
     * *取结果$result中第一行记录
     *
     * @param  <object> $result :查询结果数据集
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象;默认 false
     * @return 没有结果返回 false
     */
    public static function fetchRow($result = null, $assoc = false)
    {
        if (empty($result)) {
            return false;
        }
        if ($assoc) {
            return $result->fetch_assoc();
        } else {
            return $result->fetch_object();
        }
    }

    /**
     * *取结果(self::$result)中所有记录
     * @param <object> $result :查询结果数据集
     * @param  <string> $key_field :指定记录结果键值使用哪个字段,默认为 false 则使用 regI{0...count}
     * @param  <bool> $assoc :true 返回数组; false 返回stdClass对象;默认 false
     * @return 没有结果返回 false
     */
    public static function fetchAll($result, $keyField = false, $assoc = false)
    {
        $rows = ($assoc) ? array() : new stdClass;
        $regI = 0;
        while ($row = self::fetchRow($result, $assoc)) {
            $tmp = ($assoc) ? array() : new stdClass;
            if ($keyField != false) {
                $keyField = explode(',', $keyField);
                foreach ($keyField as $key => $value) {
                    if ($assoc) {
                        $tmp[$value] = $row[$value] ? $row[$value] : "";
                    } else {
                        $tmp->$value = $row->$value ? $row->$value : "";
                    }
                }
            } else {
                $tmp = $row;
            }
            if ($assoc) {
                $rows[$regI] = $tmp;
            } else {
                $rows->{$regI} = $tmp;
            }
            $regI++;
        }
        self::freeResult($result);
        return $rows;
    }

    /**
     * 执行更新数据操作
     *
     * @param  <string> $table  数据库表名称
     * @param  <array|stdClass> $data  待更新的数据
     * @param  <string>  $where  更新条件
     * @return 成功 true; 失败 false
     */
    public static function update($table, $data, $where)
    {
        $set = '';
        if (is_object($data) || is_array($data)) {
            foreach ($data as $k => $v) {
                $v = self::formatValue($v);
                $set .= empty($set) ? ("`{$k}` = {$v}") : (", `{$k}` = {$v}");
            }
        } else {
            $set = $data;
        }

        return self::query("UPDATE `{$table}` SET {$set} WHERE {$where}");
    }

    /**
     * 执行插入数据操作
     *
     * @param  <string> $table 数据库表名称
     * @param  <array|stdClass> $data  待更新的数据
     * @param  <array> $fields  数据库字段，默认为 null。 为空时取 $data的 keys
     * @return 成功 true; 失败 false
     */
    public static function insert($table, $data, $fields = null)
    {
        if ($fields == null) {
           
            //考虑到多条插入数据的可能性
            foreach ($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach ($v as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach ($data as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                }
                break;
            }
        }
        $_fields          = '`' . implode('`, `', $fields) . '`';
        $_data            = self::formatInsertData($data);
        self::$isInsertId = true;
        return self::query("INSERT INTO `{$table}` ({$_fields}) VALUES {$_data}");
    }

    /**
     * *格式化插入数据
     *
     * @param  $data [array|stdClass] 待格式化的插入数据
     * @return insert 中 values 后的 SQL格式
     */
    protected static function formatInsertData($data)
    {
        $output  = '';
        $is_list = false;
        //考虑到多条插入数据的可能性
        foreach ($data as $value) {
            if (is_object($value) || is_array($value)) {
                $is_list = true;
                $tmp     = '';
                foreach ($value as $v) {
                    $v = self::formatValue($v);
                    $tmp .= !empty($tmp) ? ", {$v}" : $v;
                }
                $tmp = "(" . $tmp . ")";
                $output .= !empty($output) ? ", {$tmp}" : $tmp;
                unset($tmp);
            } else {
                $value = self::formatValue($value);
                $output .= !empty($output) ? ", {$value}" : $value;
            }
        }
        if (!$is_list) $output = '(' . $output . ')';
        return $output;
    }

    /**
     * *格式化值
     *
     * @param  <string> $value 待格式化的字符串
     */
    protected static function formatValue($value)
    {
        $value = trim($value);
        if ($value === null || $value == '') {
            $value = 'NULL';
        } else {
            $value = "'" . addslashes(stripslashes($value)) . "'";
        }
        return $value;
    }

    /**
     * *返回最后一次插入的ID
     *
     * @param <object> $mysqli 当前mysql实例
     * @return <int> insert_id
     */
    public static function insertId($mysqli)
    {
        return $mysqli->insert_id;
    }

    /**
     * *返回结果集数量
     *
     * @param  $result [数据集]
     */
    public static function numRows($result)
    {
        if (is_null($result)) {
            return false;
        }
        return $result->num_rows;
    }

    /**
     * *统计表记录
     *
     * @param  <string> $table 数据库表名称
     * @param  <string> $where  SQL统计条件,默认为 1 查询整个表
     */
    public static function total($table, $where = '1')
    {
        $sql    = "SELECT count(*) FROM {$table} WHERE {$where}";
        $result = self::query($sql);
        return self::fetchScalar($result);
    }

    /**
     * *返回当前查询影响的记录数
     *
     * @param <object> $mysqli 当前mysql 实例
     */
    public static function affectedRows($mysqli)
    {
        return $mysqli->affected_rows;
    }

    /**
     * *开始事物处理,关闭MYSQL的自动提交模式
     */
    public static function commitBegin()
    {
        $mysqli = self::getDbConnect(self::$dbEnv, self::$dbType);
        self::$isError = false;
        $mysqli->autocommit(false); //使用事物处理,不自动提交
    }

    /**
     * *提交事物处理
     *
     * @param <object> $mysqli 当前mysql 实例
     */
    public static function commitEnd()
    {
        $mysqli = self::getDbConnect(self::$dbEnv, self::$dbType);
        if (!self::$isError) {
            $mysqli->commit();
        } else {
            $mysqli->rollback();
        }
        $mysqli->autocommit(true); //不使用事物处理,开启MYSQL的自动提交模式
        self::$isError = false;
    }

    /**
     * *释放数据集
     *
     * @param <object> $result :查询结果数据集
     */
    public static function freeResult($result)
    {
        if (!is_null($result)) {
            $result->free();
        }
    }

    /**
     * *选择数据库
     *
     * @param  $dbname [string] 数据库名称
     */
    public static function selectDb($dbname)
    {
        $mysqli = self::getDbConnect(self::$dbEnv, self::$dbType);
        return $mysqli->select_db($dbname);
    }

    /**
     * *日志处理
     *
     * @param  <string> $message 产生的日志消息
     */
    protected static function log($message)
    {
        //
        echo $message;
    }

}
