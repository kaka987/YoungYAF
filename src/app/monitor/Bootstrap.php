<?php
/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public $config;

    public function _initConfig() {

        $this->config = Yaf_Application::app()->getConfig();

		//把配置保存起来
        $monitor = Ym_Config::getAppItem('monitor');
        $actions = Ym_Config::getAppItem('actions');
        $alarm   = Ym_Config::getAppItem('alarm');

		Yaf_Registry::set('monitor_config', $monitor);
        Yaf_Registry::set('actions_config', $actions);
        Yaf_Registry::set('alarm_config', $alarm);
        Yaf_Registry::set('common_config', Ym_Config::getAppItem('common'));
	}

	public function _initRegisterLocalNamespace() {
		Yaf_Loader::getInstance()->registerLocalNamespace(array('Dao', 'Sys', 'User', 'View', 'Export', 'Mail'));//add by zhangy@20140604
	}

	public function _initLayout(Yaf_Dispatcher $dispatcher)
    {
        $layout = new View_Layout($this->config->application->layout->directory);
        $dispatcher->setView($layout);
    }

    public function _initAutoLoad() {
        spl_autoload_register(function($className){
            $items    = explode('_', $className);
            $layer    = strtolower(array_shift($items));
            $filename = array_pop($items) . '.php';
            $filePath = strtolower(implode('/' , $items));

            $filePath   = YPP_APP_ROOT . '/'. $layer .'s/' . $filePath . '/'. $filename;

            if (is_file($filePath)){
                Yaf_Loader::import($filePath);
                return true;
            }

            return false;
        });
    }

    public function _initSession(Yaf_Dispatcher $dispatcher)
    {
        // SESSIONID的生存时间
        ini_set('session.cookie_lifetime', 3600 * 24);
        // SESSION的生存时间
        ini_set('session.gc_maxlifetime', 3600 * 24);
        // 用户自定义SESSION保存处理
        ini_set('session.save_handler', 'User');
        // 获取自定义Session实例
        Sys_Session::getInstance();
    }

    public function _initBusiness() {
        $userBusinessService = new Service_User_Business();

        $business = $userBusinessService->getList(User_Session::getUserIdCookie());

        User_Session::setBusiness($business);
    }

	// public function _initPlugin(Yaf_Dispatcher $dispatcher) {
	// 	//注册一个插件
	// }

	// public function _initRoute(Yaf_Dispatcher $dispatcher) {
	// 	//在这里注册自己的路由协议,默认使用简单路由
	// }
	
	// public function _initView(Yaf_Dispatcher $dispatcher){
	// 	//在这里注册自己的view控制器，例如smarty,firekylin
	// }
}
