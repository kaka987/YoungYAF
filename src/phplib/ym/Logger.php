<?php
/**
 * YPP Loger Class
 *
 *
 * @author      zhangy<Young@yeahmobi.com>
 * @package     ym
 * @category    Libraries
 * @since       Version 1.0.1
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
class Ym_Logger
{

    /**
     * date famat
     */
    const DATEFAT = 'Y-m-d H:i:s';

    /**
     * log levels
     * @var array
     */
    protected static $levels = array('debug'=>1, 'info'=>2, 'notice'=>3, 'warning'=>4, 'error'=>5, 'fatal'=>6, 'alert'=>7, 'emergency'=>8);

    /**
     * log levels
     * @var array
     */
    protected static $levelsExt = array('5xx'=>9);

    /**
     * the server hostname
     * @var string
     */
    protected static $hostname = NULL;

    /**
     * log file name
     * @var string
     */
    protected static $logFile = 'monitor';
    
    /**
     * log file name
     * @var string
     */
    protected static $realLogFile = '';

    /**
     * log file path
     * @var string
     */
    protected static $logPath = '/tmp/';

    /**
     * log handler 
     * the log type or method
     * 
     * @var string
     */
    protected static $handler = 'file';

    /**
     * log handler cache
     * 
     * @var source
     */
    protected static $handlerCache = NULL;

    /**
     * if cache the log handler
     * @var [type]
     */
    protected static $ifCacheHandler = FALSE;

    /**
     * The log file type [error|info]
     * @var [type]
     */
    protected static $fileType = 'info';

    //-----------------------------------------------------------------------

    /**
     * init the log object
     * 
     * @param  array  $logConfig 
     * @return object
     */
    public static function init(array $logConfig = array()) {

        // Set the default log config
        // $logConfig = array('logPath'=>'./', 'logFile'=>'yeahmobi_'.$fileLevel, 'handler'=>'file', 'ifCacheHandler'=>TRUE);
        $logConfig['logPath'] = isset($logConfig['logPath']) ? $logConfig['logPath'] : static::$logPath;
        $logConfig['logFile'] = isset($logConfig['logFile']) ? $logConfig['logFile'] : static::$logFile;
        $logConfig['handler'] = isset($logConfig['handler']) ? $logConfig['handler'] : static::$handler;
        $logConfig['levels']  = isset($logConfig['levels']) ? $logConfig['levels'] : static::$levels;
        $logConfig['levelsExt']  = isset($logConfig['levelsExt']) ? $logConfig['levelsExt'] : static::$levelsExt;
        $logConfig['ifCacheHandler'] = isset($logConfig['ifCacheHandler']) ? $logConfig['ifCacheHandler'] : static::$ifCacheHandler;
        // get and set the hostname
        static::getHostname();
        // configure log
        static::doConfigure($logConfig);
	}

    /**
     * the real place of logging
     * 
     * @param  string $msg     
     * @param  string $level   
     * @param  string $from    
     * @param  string $extends 
     * @return boolean
     */
    public static function log($msg='', $level='info', $from='', $extends='') {

        if (empty($msg)) return FALSE;

        $time = static::getTime();
		$logLevel = static::getLevelName($level);
		if ( ! $logLevel) return FALSE; //throw error

        // Set the log file type
        static::getFileType($level);

		// get from Info START>>>
        if (empty($from)) {

    		$traces=debug_backtrace();
    		$count=0;
    		foreach($traces as $trace) {

                if (isset($trace['file'],$trace['line'])) {
                    $from = $trace['file'].':'.$trace['line'];
                }

    			if(++$count>=2)
    				break;
    		}
        }
		// END<<<

		$data = array(
				'time' 		=> $time,
				'logLevel' 	=> $logLevel,
				'from' 		=> $from,
				'extends' 	=> $extends,
				'msg'		=> $msg,
			);
		$message = static::dataFormat($data);
		if ($message === FALSE) return FALSE;
		// Start log to somewhere
		if (static::logToResource($message)) {
			return TRUE;
		}
		return FALSE;
	}

    /**
     * Get the log file type
     * 
     * @param  string $level [description]
     * @return [type]        [description]
     */
    protected static function getFileType($level='info') {

        $fileType = $level;
        switch ($level) {

            case 'info':
            case 'debug':
            case 'notice':
                $fileType = 'access';
                break;
            case 'warning':
            case 'error':
            case 'fatal':
            case 'alert':
            case 'emergency':
                $fileType = 'error';
                break;

        }
        static::$realLogFile = static::$logFile.'_'.$fileType;
    }

    /**
     * info log
     *
     * @param  string $msg
     * @param  string $extends
     */
    public static function info($msg='', $extends='') {

        static::log($msg, __FUNCTION__, $from='', $extends);
    }

    /**
     * debug log
     * 
     * @param  string $msg     
     * @param  string $extends 
     */
    public static function debug($msg='', $extends='') {

        static::log($msg, __FUNCTION__, $from='', $extends);
    }

    /**
     * warning log
     * 
     * @param  string $msg     
     * @param  string $extends 
     */
    public static function warning($msg='', $extends='') {

        static::log($msg, __FUNCTION__, $from='', $extends);
    }

    /**
     * error log
     * 
     * @param  string $msg     
     * @param  string $extends 
     */
    public static function error($msg='', $extends='') {

        static::log($msg, __FUNCTION__, $from='', $extends);
    }

    /**
     * fatal log
     * 
     * @param  string $msg     
     * @param  string $extends 
     */
    public static function fatal($msg='', $extends='') {

        static::log($msg, __FUNCTION__, $from='', $extends);
    }

    /**
     * other log
     *
     * @param  string $msg
     * @param  string $extends
     */
    public static function other($msg='', $level='', $extends='') {

        static::log($msg, $level, $from='', $extends);
    }

    /**
     * log to the resource
     * 
     * @param  string $message 
     * @return boolean
     */
	protected static function logToResource($message = '') {

        if (empty($message)) return FALSE;

		switch (static::$handler) {
			case 'file':
				$resource = static::getFile();
				static::logToFile($message, $resource);
				break;

            case 'socket':
                break;
			
			default:
				$resource = static::getFile();
				static::logToFile($message, $resource);
				break;
		}

		return TRUE;
	}

    /**
     * parse the config
     * 
     * @param  array  $logConfig
     */
	protected static function doConfigure(array $logConfig = array()) {

		if (empty($logConfig)) {
			return FALSE;
		}

        foreach ($logConfig as $key=>$item) {
            static::$$key = $item;
        }
	}

    /**
     * formate the log data
     * 
     * @param  array $data
     * @return string
     */
	protected static function dataFormat(array $data = array()) {
		//Need to transfer spacial string
        if (empty($data['extends'])) {
            unset($data['extends']);
        } else {
            if (is_array($data['extends'])) {
                $data['extends'] = implode(':', $data['extends']);
            }
        }

		foreach ($data as $key => $value) {
            $data[$key] = '['.str_replace(array('[',']'), array('\[','\]'), $value).']';
        }

		return implode($data)."\n";
	}

    /**
     * log to a file
     * 
     * @param  string $message
     * @param  mixed $file    
     * @return boolean
     */
	protected static function logToFile($message='', $file=NULL) {

        $fp = NULL;

        if (static::$ifCacheHandler) { //If set to cache the handler

            if (static::$handlerCache === NULL) {
                if ( ! static::$handlerCache = @fopen($file, 'a+')) {
                    echo 'fopen file error!',PHP_EOL;
                    return FALSE;
                }
            }
            $fp = static::$handlerCache;

        } else { // If don't cache the handler

            if ( ! $fp = @fopen($file, 'a+')) {
                echo 'fopen file error!',PHP_EOL;
                return FALSE;
            }
        }

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		
        if (static::$ifCacheHandler === FALSE) {
            fclose($fp);
        }

		@chmod($file, 0777);
		return TRUE;
	}

    /**
     * get the file resource
     * 
     * @return string
     */
	protected static function getFile() {

		if (static::$realLogFile AND static::$logPath) {

            if ( ! file_exists(static::$logPath)) mkdir(static::$logPath, 0777, TRUE);
			return rtrim(static::$logPath,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.static::$realLogFile.'.log';
		}
		return FALSE;//throw error
	}

    /**
     * get the log level name 
     * 
     * @param  string $level
     * @return string
     */
	protected static function getLevelName($level) {

		return (isset(static::$levels[$level]) OR isset(static::$levelsExt[$level])) ? $level : NULL;
	}

    /**
     * get the log time
     * 
     * @return string
     */
	protected static function getTime() {

		date_default_timezone_set('PRC');
		return date(static::DATEFAT, time());
	}

    /**
     * get the hostname
     * @return string
     */
	protected static function getHostname() {

		if (static::$hostname) return static::$hostname;
		return static::$hostname = isset($_SERVER['HOSTNAME']) ? $_SERVER['HOSTNAME'] : NULL;
	}
}
// END Ym_Loger class

/* End of file Loger.php */
/* Location: ./ypp/ym/Loger.php */

/*class Logger_test {
    
    public function __construct() {

        $logConfig = array('logPath'=>'/tmp/', 'logFile'=>'monitor', 'handler'=>'file', 'levels'=>array(),'ifCacheHandler'=>TRUE);
        Logger::init($logConfig);

        $msg = 'test log!';             // log message
        //$extends = array('key_extends','value_extends'); // optional
        //$extends = 'key:value';
        //Logger::info($msg,$extends);
        Logger::info($msg);
        Logger::debug($msg);
        Logger::warning($msg);
        Logger::error($msg);
        Logger::fatal($msg);
    }
}
new Logger_test;*/