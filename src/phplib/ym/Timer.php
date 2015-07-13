<?php
/**
 * 
 */

class Ym_Timer
{

    static public $myTime = NULL;

	static public function startRecord(){

        static::$myTime = microtime(TRUE);
	}
	
	static public function getResult($type='cost'){

        if ($type=='cost')
            return microtime(TRUE) - static::$myTime;
        if ($type=='mem')
            return memory_get_peak_usage(TRUE)/1024 ;
	}
}