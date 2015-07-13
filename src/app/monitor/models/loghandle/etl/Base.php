<?php

/**
 * Class Model_Loghandle_Etl_Base
 */
abstract class Model_Loghandle_Etl_Base
{
    public $dao;

    public function __construct($logType) {
        $this->dao = new Dao_Medoo();
    }

    abstract public function transform($data);

    abstract public function load($data, $time);

}