<?php

/**
 * Class Model_Loghandle_Datasource_Factory
 */
class Model_Loghandle_Etl_Factory
{
    public static function create($logType, $mode)
    {
        $instance = null;

        $className = 'Model_Loghandle_Etl_' . ucfirst($logType) . '_' . ucfirst($mode);

        if (class_exists($className)) {
            $refObj = new ReflectionClass($className);

            $instance = $refObj->newInstance($logType);
        }

        return $instance;
    }
} 