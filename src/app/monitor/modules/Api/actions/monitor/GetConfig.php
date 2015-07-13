<?php
class Action_GetConfig extends Yaf_Action_Abstract
{
    public $model;
    public $content;
    public $server;
    public $service = array();
    public $url = 'http://54.179.179.179/static/config/';
    public $path = '/dianyi/app/ypp1.0/webroot/monitor/config/';
    public $getUrl;

    function execute() {

        $this->server = array(
            'SIN' => array(
                array('host'=>'AMZ_SIN_YeahMonitor_240_11','ip'=>'10.2.240.11'),
                array('host'=>'AMZ_SIN_YeahMonitor_240_10','ip'=>'10.2.240.10'),
            ),
            'IAD' => array(
                array('host'=>'AMZ_IAD_YeahMonitor_240_11','ip'=>'10.1.240.11'),
                array('host'=>'AMZ_IAD_YeahMonitor_240_31','ip'=>'10.1.240.31'),
            ),
            'NCA' => array(
                array('host'=>'AMZ_NCA_YeahMonitor_240_11','ip'=>'10.3.240.11'),
                array('host'=>'AMZ_NCA_YeahMonitor_240_30','ip'=>'10.3.240.30'),
            ),
            'SP' => array(
                array('host'=>'AMZ_SP_YeahMonitor_240_30','ip'=>'10.5.240.30'),
                array('host'=>'AMZ_SP_Zabbix_255_21',     'ip'=>'10.5.255.21'),
            ),
            'LON' => array(
                array('host'=>'RS_LON_YeahMonitor_240_11','ip'=>'10.10.240.11'),
                array('host'=>'RS_LON_YeahMonitor_240_30','ip'=>'10.10.240.30'),
            ),
        );

        $hostname = $this->getRequest()->getQuery("hostname");
        $this->model = new Model_Alarm_GetConfig();

        $data = $this->getLogConfig($hostname);
        if (empty($data)) {
            echo 'No data for : '.$hostname;
            exit;
        }

        $r = $this->parseConfig($data);

        $regionArr = explode('-', $hostname);
        $region = isset($regionArr[1]) ? $regionArr[1] : 'SIN';
        $this->writeMatch($this->server[$region]);

        $name = $hostname.'.'date("YmdHis").".td-agent.conf";
        if (!empty($this->content)) $this->writeConfig($name);
        echo $this->getUrl;
        exit;
    }

    public function getLogConfig($host) {

        return $this->model->getLogConfig($host);
    }

    public function parseConfig($data) {

        $config = array();

        foreach($data as $k=>$logconfig) {

            $comeArr = explode('-',$logconfig['hostname']);
            $come = isset($comeArr[4]) ? $comeArr[4] : 0;
            $config['tag'] = $logconfig['app'].'.'.$logconfig['service'].'.'.$come.'.'.$logconfig['logid'];
            $config['path'] = $logconfig['logpath'];
            $config['app'] = $logconfig['app'];

            if ( ! in_array($logconfig['app'], $this->service) )
                $this->service[] = $logconfig['app'];

            $this->writeSource($config);
        }
    }

    public function writeConfig($name) {

        $file = $this->path.$name;
        $this->getUrl = $this->url.$name;
        return file_put_contents($file,$this->content);
    }

    public function writeSource($config) {

        $this->content .= <<<EOF
<source>
    type        tail
    path        {$config['path']}
    pos_file    /tmp/{$config['app']}.log.pos
    tag         {$config['tag']}
    format      none
</source>

EOF;

    }

    public function writeMatch($config) {

        foreach($this->service as $s) {
            $this->content .= <<<EOF
<match {$s}.*.*.*>
    type               forward
    send_timeout       60s
    recover_wait       10s
    heartbeat_interval 10s
    phi_threshold      16
    hard_timeout       60s
    flush_interval     1s

    <server>
        name    {$config[0]['host']}
        host    {$config[0]['ip']}
        port    24224
    </server>
    <server>
        name    {$config[1]['host']}
        host    {$config[1]['ip']}
        port    24224
        standby
    </server>
</match>

EOF;
        }

    }
} 