<?php

/**
 * Class Model_Loghandle_Cache_Factory
 */
class Model_Loghandle_Cache_Factory
{
    public static function create($logType, $name)
    {
        $instance = null;

        $className = 'Model_Loghandle_Cache_' . ucfirst($logType) . '_' . ucfirst($name);

        if (class_exists($className)) {
            $refObj = new ReflectionClass($className);

            $instance = $refObj->newInstance();
        }

        return $instance;
    }
} 