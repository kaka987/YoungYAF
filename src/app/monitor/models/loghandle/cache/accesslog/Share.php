<?php
class Model_Loghandle_Cache_Accesslog_Share extends Model_Loghandle_Cache_Base
{
    public $m;

    public function __construct() {

        parent::__construct();

        $config  = Yaf_Registry::get('monitor_config');
        $server  = explode(',', trim($config['share']['server']));
        $servers = null;

        foreach ($server AS $serverStr) {

            $servers[] = explode(":", trim($serverStr));
        }

        $this->m = new Memcached();
        $this->m->addServers($servers);
    }

    public function put($data) {

        echo "share data: " . count($data['value']) ."\r\n";

        $this->m->set($data['key'], $data['value'], 30);

        echo "share count:" . count($this->m->getAllKeys());
    }

    public function get()
    {
        $data = null;

        return $data;
    }
}