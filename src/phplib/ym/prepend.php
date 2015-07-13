<?php
/**
 * YPP的预编译文件
 *
 * 1. 定义基础变量
 * 2. 注册全局类库路径
 * 
 * @author      Young <young@1988de.com>
 * @version     1.0.0
 * @package     ym
 * @category    script
 *
 */
// 定义基础变量
define('YPP_ROOT', realpath(dirname(__FILE__) . '/../../'));
define('YPP_VERSION', '1.0.1');
define('YPP_DIR_APP', YPP_ROOT . '/app');
define('YPP_DIR_DAT', YPP_ROOT . '/storage');
define('YPP_DIR_LOG', YPP_DIR_DAT . '/logs');
define('YPP_DIR_CONF', YPP_ROOT . '/conf');
define('YPP_ROOT_PHPLIB', YPP_ROOT . '/phplib');

// 加载入口类文件
Yaf_Loader::getInstance(YPP_ROOT_PHPLIB);
/* Location：phplib/ym/prepend.php */