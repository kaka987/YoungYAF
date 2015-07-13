<?php
class Model_User_Online
{
    public $userOnlineTable;
    public $dao;

    public function __construct() {
        $this->dao = new Ym_Dao('default');

        $this->userOnlineTable = Sys_Database::getTable('user_online');
    }

    /**
     * 获取在线用户总数
     * @param $userOnline
     * @return number
     */
    public function getUserCount($userOnline = array()) {
        $where = "";

        if (isset($userOnline['email'])) {
            $where .= "`email` like '". $userOnline['email'] ."%'";
        }

        $where = empty($where) ? $where : "WHERE " . $where;

        $sql = "SELECT count(*) as num FROM {$this->userOnlineTable} {$where}";
        $result = $this->dao->queryRow($sql, true);

        return $result['num'];
    }

    /**
     * 获取在线用户列表
     * @param $userOnline
     * @param $skip
     * @param $limit
     * @return array
     */
    public function getUserList($userOnline = array(), $skip, $limit) {
        $where = "";

        if ($userOnline['email']) {
            $where .= "`email` like '". $userOnline['email'] ."%'";
        }

        $limit = "LIMIT {$skip},{$limit}";

        $where = empty($where) ? $where : "WHERE " . $where;

        $sql = "SELECT * FROM {$this->userOnlineTable} {$where} {$limit}";

        $result = $this->dao->fetchAll($sql, false, true);

        return $result;
    }

    /**
     * 获取用户
     * @param $uid
     * @return 失败返回
     */
    public function getUser($uid) {
        $sql = "SELECT * FROM {$this->userOnlineTable} WHERE `uid` = {$uid}";
        $result = $this->dao->queryRow($sql, true);

        return $result;
    }

    /**
     * 删除离线用户
     */
    public function removeOfflineUser() {
        $delTime = time() - 60;

        $where = sprintf("`active_time` < %d", $delTime);

        $this->dao->delete($this->userOnlineTable, $where);
    }

    /**
     * 刷新在线用户
     * @param $userOnline
     * @return boolean
     */
    public function refreshUser($userOnline) {
        $returns = null;

        $where = sprintf("`uid` = %d", $userOnline['uid']);

        $data = array(
            'total_time'  => $userOnline['totalTime'],
            'active_time' => $userOnline['activeTime']
        );

        $returns = $this->dao->update($this->userOnlineTable, $data, $where);

        return $returns;
    }

    /**
     * 新增在线用户
     * @param $userOnline
     * @return void|成功
     */
    public function addUser($userOnline) {
        $data = array(
            'uid'         => $userOnline['uid'],
            'alias_name'  => $userOnline['aliasName'],
            'full_name'   => $userOnline['fullName'],
            'email'       => $userOnline['email'],
            'active_time' => time(),
            'total_time'  => 0,
            'ip'          => $userOnline['ip']
        );

        return $this->dao->insert($this->userOnlineTable, $data);
    }

    /**
     * 删除在线用户
     * @param $uid
     * @return bool
     */
    public function delUser($uid) {
        $where = sprintf("`uid` = %d", $uid);

        return $this->dao->delete($this->userOnlineTable, $where);
    }
}