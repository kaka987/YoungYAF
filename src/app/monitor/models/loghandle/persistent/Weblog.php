<?php
class Model_Loghandle_Persistent_Weblog
{
    private $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function getAppId() {
        $returns = array();

        $join   = array( '[>]'.Sys_Database::getTable('monitor_logconfig') => array( 'monitor_app' => 'id' ) );
        $column = array( Sys_Database::getTable('monitor_logconfig').'.id', Sys_Database::getTable('monitor_logconfig').'.monitor_app', Sys_Database::getTable('monitor_alarmconfig').'.monitor_param' );
        $where  = array(
            'AND' => array(
                Sys_Database::getTable('monitor_alarmconfig').'.monitor_status' => 1,
                Sys_Database::getTable('monitor_alarmconfig').'.monitor_api' => 'check_weblog_key'
            )
        );

        $config = $this->dao->select(Sys_Database::getTable('monitor_alarmconfig'), $join, $column, $where );

        foreach ( $config AS $app ) {
            $returns[$app['monitor_app']][$app['id']] = $app['monitor_param'];
        }

        return $returns;
    }
} 