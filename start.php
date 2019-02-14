<?php
// +----------------------------------------------------------------------
// | HYYPHP [ WE CAN DO IT JUST HYYPHP ]
// +----------------------------------------------------------------------
// | Copyright (c) HanSheng All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: HanSheng <164897033@qq.com>
// +----------------------------------------------------------------------
header("Content-Type: text/html;charset=utf-8");
header("X-Powered-By: HYYPHP");

/*
 * ------------------------------------------------------
 *  定义常量
 * ------------------------------------------------------
 */
define('HYYPHP_START_TIME', microtime(true));
define('HYYPHP_START_MEM', memory_get_usage());
define('DS', DIRECTORY_SEPARATOR);
define('HYYPHP_VERSION', '0.0.1-Beat');
define('EXT', '.php');
defined('DEBUG') or define('DEBUG', false);
defined('MODULE') or define('MODULE', '');
defined('HYY_PATH') or define('HYY_PATH', __DIR__ . DS);
define('LIB_PATH', HYY_PATH . 'lib' . DS);
define('COMMON_PATH', HYY_PATH . 'common' . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH . 'config' . DS); // 配置文件目录
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
defined('LOG_PATH') or define('LOG_PATH', ROOT_PATH . 'logs' . DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);

define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);

/*
 * ------------------------------------------------------
 *  环境常量 Windows / Liunx
 * ------------------------------------------------------
 */
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

/*
 * ------------------------------------------------------
 *  设置时区
 * ------------------------------------------------------
 */
ini_set('date.timezone','Asia/Shanghai');

/*
 * ------------------------------------------------------
 *  载入公共函数库
 * ------------------------------------------------------
 */
require_once COMMON_PATH . 'function.php';

/*
 * ------------------------------------------------------
 *  Debug
 * ------------------------------------------------------
 */
if(DEBUG) {
    error_reporting(E_ALL | E_STRICT);
    ini_set("display_errors", "On");
}else {
    ini_set('display_error', 'Off');
}

/*
 * ------------------------------------------------------
 *  载入Loader类
 * ------------------------------------------------------
 */
require_once LIB_PATH . 'Loader.php';

/*
 * ------------------------------------------------------
 *  注册自动加载类方法
 * ------------------------------------------------------
 */
hyyphp\Loader::register();

/*
 * ------------------------------------------------------
 *  启动框架
 * ------------------------------------------------------
 */
try {
    hyyphp\App::run();
} catch (\Exception $e) {
    echo $e->getMessage(); exit();
}
