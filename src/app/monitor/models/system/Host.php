<?php
class Model_System_Host
{
    /**
     * region table
     * @var string
     */
    public $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getHostId($host) {
        $hostId = null;

        $host = explode(",", $host);
        $host = implode("','", $host);
        $host = empty($host) ? '' : "'" . $host . "'";

        $column = "id";
        $where  = "WHERE `host` in (" . $host . ")";

        $result = $this->dao->select(Sys_Database::getTable('relation_host'), $column, $where);

        if ($result) {
            foreach($result as $value){
                $hostId .= $value .",";
            }

            $hostId = rtrim($hostId, ',');
        }

        return $hostId;
    }

    public function getHostPathId($host,$path=NULL) {

        $sql = "select id as path_id,host_id from relation_path where host='".$host."' and path='".$path."'";
        if (empty($path)) $sql = "select id as path_id,host_id from relation_path where host='".$host."' limit 1";

        $r = $this->dao->query($sql, TRUE);
        return $r->fetchAll(PDO::FETCH_ASSOC);
    }

}
