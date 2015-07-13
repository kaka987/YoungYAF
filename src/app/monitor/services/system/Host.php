<?php
class Service_System_Host
{
    private $systemHostModel;

    public function __construct() {
        $this->systemHostModel = new Model_System_Host();
    }

    /**
     * 获取HostId
     */
    public function getHostId($host) {
        $hostId = null;

        $hostId = $this->systemHostModel->getHostId($host);

        return $hostId;
    }

    public function getHostPathId($host,$path) {

        return $this->systemHostModel->getHostPathId($host,$path);
    }
} 
