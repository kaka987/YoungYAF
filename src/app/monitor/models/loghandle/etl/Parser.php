<?php

class Model_Loghandle_Etl_Parser
{
    public $dao;

    public function __construct() {
        $this->dao = new Dao_Medoo();
    }

    public function extract(&$originData) {

        $returns = array();

        $pattern = '/^(?<request_time>[^ ]*) (?<remote>[^ ]*) (?<host>[^ ]*) "(?<server>[^\"]*)" "(?<server_status>[^\"]*)" \[(?<time>[^\]]*)\] "(?<method>[A-Z]*)? ?(?:(?<path>[^\"]*) +\S*)?" (?<status>[^ ]*) (?<size>[^ ]*)(?: "(?<referer>[^\"]*)" "(?<agent>[^\"]*)" "(?<forward>[^\"]*)" (?<response_time>.*))?$/';
        //var_dump(Ym_Config::getAppItem('monitor:parse.filter.host'));
//        $pattern = '/^(?P<request_time>[^ ]*) (?P<remote>[^ ]*) (?P<host>[^ ]*) "(?P<server>[^\"]*)" "(?P<server_status>[^\"]*)" \[(?P<time>[^\]]*)\] "(?P<method>\S+)(?: +(?P<path>[^\"]*) +\S*)?" (?P<status>[^ ]*) (?P<size>[^ ]*)(?: "(?P<referer>[^\"]*)" "(?P<agent>[^\"]*)" "(?P<forward>[^\"]*)" (?P<response_time>.*))?$/';

        $filer = Ym_Config::getAppItem('monitor:parse.filter.host');
        $filterHost = isset($filer) ? $filer : '-';
        $filterHostArr = explode(',',$filterHost);
        foreach ( $originData AS $val) {
            preg_match($pattern, $val['message'], $matches);
			//var_dump($pattern, $val['message'], $matches);
            $path = strstr($matches['path'], '?', true);

            $matches['host']   = isset($matches['host']) ? $matches['host'] : '';
            echo $matches['host'].PHP_EOL;
            if (in_array($matches['host'], $filterHostArr)) continue;
            echo '-----',$matches['host'].PHP_EOL;
            $matches['path']   = empty($path) ? $matches['path'] : $path;
            $matches['time']   = strtotime($matches['time']);

            // responseTime有时会出现用,分割的两个值
            if ( strpos($matches['response_time'], ',') ) {
                $matches['response_time'] = strstr($matches['response_time'], ',', true);
            }

            $hostId   = $this->getHostId($matches['host']);
            $pathId   = $this->getPathId($hostId, $matches['host'], $matches['path']);
            $serverId = $this->getServerId($matches['server']);

            $data = array(
                'request_time'  => $matches['request_time'],
                'remote'        => $matches['remote'],
                'host'          => $matches['host'],
                'time'          => $matches['time'],
                'method'        => $matches['method'],
                'path'          => $matches['path'],
                'status'        => $matches['status'],
                'size'          => $matches['size'],
                'referer'       => $matches['referer'],
                'agent'         => $matches['agent'],
                'forward'       => $matches['forward'],
                'response_time' => $matches['response_time'],
                'server_id'     => $serverId,
                'server_status' => $matches['server_status'],
                'host_id'       => $hostId,
                'path_id'       => $pathId
            );

            $returns[] = $data;
        }

        return $returns;
    }

    public function getPathId($hostId, $host, $path) {
        $returns = null;

        $columns = array('id');
        $where = sprintf("WHERE `host_id` = %d AND `path` = '%s'", $hostId, $path);

        $rowData = $this->dao->get(Sys_Database::getTable('relation_path'), $columns, $where);

        if ($rowData['id'] > 0) {
            $returns = $rowData['id'];
        } else {
            $data = array(
                'host_id' => $hostId,
                'host'    => $host,
                'path'    => $path
            );

            $returns = $this->dao->insert(Sys_Database::getTable('relation_path'), $data);
        }

        return $returns;
    }

    public function getHostId($host) {
        $returns = null;

        $columns = array('id');
        $where = sprintf("WHERE `host` = '%s'", $host);

        $rowData = $this->dao->get(Sys_Database::getTable('relation_host'), $columns, $where);

        if ($rowData) {
            $returns = $rowData['id'];
        } else {
            $data = array(
                'host' => $host,
            );

            $returns = $this->dao->insert(Sys_Database::getTable('relation_host'), $data);
        }

        return $returns;
    }

    public function getServerId($server) {
        $returns = null;

        $columns = array('id');
        $where = sprintf("WHERE `server` = '%s'", $server);

        $rowData = $this->dao->get(Sys_Database::getTable('relation_server'), $columns, $where);

        if ($rowData) {
            $returns = $rowData['id'];
        } else {
            $data = array(
                'server' => $server,
            );

            $returns = $this->dao->insert(Sys_Database::getTable('relation_server'), $data);
        }

        return $returns;
    }
}