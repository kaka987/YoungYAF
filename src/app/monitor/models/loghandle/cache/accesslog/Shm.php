<?php

class Model_Loghandle_Cache_Accesslog_Shm extends Model_Loghandle_Cache_Base
{
    public $flag = 'a';

    public $shmDao;

    public function __construct()
    {
        parent::__construct();

        $this->shmDao = new Dao_ShareMemory($this->flag);
    }

    public function put($data)
    {
        $this->shmDao->put($data);
    }

    public function get()
    {
        $data = $this->shmDao->get();

        return $data;
    }
} 