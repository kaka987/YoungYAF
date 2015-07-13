<?php
/**
 * Yaf client command
 *
 * @author      Xuexb<jeffxiaobo@foxmail.com>
 * @package     xiaobo/sh
 * @since       Version 1.0.1 @20140423
 * @copyright   Copyright (c) 2014, Yeahmobi, Inc.
 */
date_default_timezone_set('Asia/Shanghai');


define('YPP_APP', 'yeahmonitor');
define('YPP_APP_ROOT', YPP_DIR_APP . '/' . YPP_APP);
define('YPP_APP_LIB', YPP_APP_ROOT . '/library');
// 声明loader
Yaf_Loader::getInstance(YPP_APP_LIB, YPP_ROOT_PHPLIB);

// 初始化配置和日志类
Ym_Config::init();
$logConf['logPath'] = YPP_DIR_LOG . '/app/' . YPP_APP;
$logConf['logFile'] = YPP_APP;
Ym_Logger::init($logConf);
		
// 加载application.ini
$app = new Yaf_Application(YPP_DIR_CONF . '/app/' . YPP_APP . '/application.ini');
$app->bootstrap();
$app->getDispatcher()->dispatch(new Yaf_Request_Simple());

//方法2：不试用带module的模式
//$controller = isset($argv[1]) ? $argv[1] : '';
//$action     = isset($argv[2]) ? $argv[2] : '';
//if (!$controller || !$action) {
//    die('Please Use like this: /dianyi/app/ypp/bin/php yafClient.php controller action [argv]' . PHP_EOL);
//}
//$argv = isset($argv[3]) ? $argv[3] : '';
//$res = $app->execute(array('Controller_' . $controller, $action . 'Action'), $argv);
