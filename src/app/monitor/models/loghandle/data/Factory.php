<?php

/**
 * Class Model_Loghandle_Data_Factory
 */
class Model_Loghandle_Data_Factory
{
    public static function create($logType, $name)
    {
        $instance = null;

        $className = 'Model_Loghandle_Data_' . ucfirst($logType) . '_' . ucfirst($name);

        if (class_exists($className)) {
            $refObj = new ReflectionClass($className);

            $instance = $refObj->newInstance($logType);
        }

        return $instance;
    }
} 