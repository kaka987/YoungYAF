<?php
/**
 * 初始化文件
 * 
 * 所有文件的入口调用
 * 1. 初始化app相关数据，包括：
 * 	  配置常量
 * 	  yaf实例
 * 	  loader注册
 * 2. 注册退出时的事件 以及 事件的回调函数
 * 
 * @author      Young <young@1988de.com>
 * @version     1.0.0
 * @package     ym
 * @category    script
 */

class Ym_Init
{

	static private $instance = NULL;	//单例的实例化载体
	static private $app = NULL;			//当前app的实例


    //-------------------------------------------------------------------

    private function __construct(){}	//单例

    /**
     * 使用Ym框架时的初始化
     *
     * 这里需要指定app的名称，若不指定，则抛弃与app有关的初始化
     * 届时，与app有关的常量，也都不会定义
     *
     * 1. 初始化Yaf对象
     * 2. 注册autoloader以及shutdown事件
     *
     * @param string $app app的名称，与目录名一致
     *
     * @return Ym_Init
     */
    static public function init($app = NULL){

        if(self::$instance === NULL){

            self::initApp($app);
            self::initRegisterAutoloader();
            self::initRegisterShutdownEvent();
            self::$instance = new Ym_Init();
        }

        return self::$instance;
    }

	/**
	 * 这里主要指对app的run规则的调用
	 * 
	 * 实际上，仅在指定了app的情况下，下面的逻辑才实际有效
	 * run会调用yaf的application::run
	 * 执行yaf的实际逻辑
	 */
	public function run(){

        self::$app->bootstrap()->run();
	}
	
	/**
	 * 初始化APP的数据
	 * 
	 * @param string $app 启用的app的名称，如：test
	 */
	static private function initApp($app){

		if(empty($app)) return FALSE;

        Ym_Timer::startRecord();

		define('YPP_APP', $app);
		define('YPP_APP_ROOT', YPP_DIR_APP . '/' . YPP_APP);
		define('YPP_APP_LIB', YPP_APP_ROOT . '/library');
        define('YPP_APP_LOG', YPP_DIR_LOG . '/' . YPP_APP);

		// 声明loader，加载APP类文件
		Yaf_Loader::getInstance(YPP_APP_LIB);
		
		// 初始化配置
		Ym_Config::init();
        $env = Ym_Config::getAppItem('env:monitor.env');
        if ($env) define('YPP_APP_ENV', $env);
        else define('YPP_APP_ENV', 'dev');

        // 初始化日志
        if (YPP_APP_ENV == 'pro') $logConf['levels'] = array('debug'=>1,  'warning'=>4, 'error'=>5, 'fatal'=>6, 'alert'=>7, 'emergency'=>8);
		$logConf['logPath'] = YPP_APP_LOG;
		$logConf['logFile'] = YPP_APP;
		Ym_Logger::init($logConf);
        //Ym_Logger::info('test');

		// 加载application.ini
		self::$app = new Yaf_Application(YPP_DIR_CONF . '/app/' . YPP_APP . '/application.ini');
	}

	/**
	 * 定义新的自己的命名空间
	 *
	 * 将models目录加入到autoload中去
	 */
	static private function initRegisterAutoloader(){

		spl_autoload_register(

            function($classname){
                $classItems = explode('_', $classname);
                $filename = array_pop($classItems) . '.php';
                array_unshift($classItems, 'models');
                $filepath = implode('/' , $classItems);
                $filepath = strtolower($filepath);

                $realFilepath = YPP_APP_ROOT . '/' . $filepath . '/'. $filename;
                if(is_file($realFilepath)){
                    Yaf_Loader::import($realFilepath);
                    return TRUE;
                }
                return FALSE;
		    }
        );
	}
	
	/**
	 * 注册运行结束时的
	 */
	static private function initRegisterShutdownEvent(){

        register_shutdown_function(__CLASS__ . '::execShutdownCallback');
	}
	
	/************************************************
	 * CALLBACKS
	 ************************************************
	 */
	
	/**
	 * 运行完成后的回调
	 * 
	 * 1. log中增加 时间消耗、内存消耗 统计
	 * 2. 输出日志
	 */
	static public function execShutdownCallback(){

        // if (YPP_APP_ENV != 'pro') echo 'cost : '. Ym_Timer::getResult() . ' s , mem  : '. memory_get_peak_usage(TRUE)/1024 . ' KB', PHP_EOL;
		// Ym_Logger::info('cost : '. Ym_Timer::getResult(). 's'); //为什么这里日志会写两次？ 放至index.php
		// Ym_Logger::info('mem : '. memory_get_peak_usage(TRUE)/1024 . 'KB'); //单位：Bytes
	}
}
