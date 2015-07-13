<?php

/**
 * Class Model_Loghandle_Cache_Base
 */
abstract class Model_Loghandle_Cache_Base
{
    public function __construct()
    {
        // TODO
    }

    abstract public function put($data);

    abstract public function get();
} 