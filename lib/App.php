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

namespace hyyphp;

class App {

    protected static $controller;          //默认控制器
    protected static $method;              //默认方法
    protected static $pams     = array();  //其他参数

    /**
     * 项目的入口方法
     * @throws Exception
     */
    public static function run() {
        // App公共
        t('HYYPHP_FUNC_TIME', '', microtime(1) - HYYPHP_START_TIME);

        // 路由解析
        self::parseUrl();

        // 执行操作
        self::execHyy();

        // 总的运行时间
        t('HYYPHP_TIME', '', microtime(1) - HYYPHP_START_TIME);

        if (!Config::get('config', 'debug_trace') || IS_AJAX || IS_CLI) exit;

        Log::show();

    }

    /**
     * url重写路由的URL地址解析方法
     */
    protected static function parseUrl() {
        t('HYYPHP_ROUTE_TIME');

        //判断是否传了URL
        if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/'){
            // 解析 /index/index
            $path = $_SERVER['REQUEST_URI'];

            // 过滤?之后的参数
            $path = preg_replace("/\?.*/", "", $path);
            $url = explode('/', trim($path, '/'));
            $pathInfo = Config::get('config', 'PATH_INFO');
            // dump($pathInfo); exit;
            if($pathInfo == 1) {
                //得到控制器名称
                if (isset($url[0])) {
                    self::$controller = ucfirst($url[0]); //首字母大写
                    unset($url[0]);
                }
                //得到方法名
                if (isset($url[1])) {
                    self::$method = $url[1];
                    unset($url[1]);
                }else {
                     self::$method = Config::get('config', 'default_function');
                }
            }else {
                //得到控制器名称
                $controller = I('get.c', Config::get('config', 'default_controller'));
                self::$controller = ucfirst($controller);
                //得到方法名
                self::$method = I('get.a', Config::get('config', 'default_function'));
            }

            //判断是否还其他的参数
            if (isset($url)) {
                self::$pams = array_values($url);
            }
        }else {
            self::$controller = Config::get('config', 'default_controller');
            self::$method = Config::get('config', 'default_function');
        }

        define('CONTROLLER_NAME', self::$controller);
        define('FUNCTION_NAME', self::$method);

        t('HYYPHP_ROUTE_TIME', 0);
    }

    /**
     * 执行操作
     * @throws \Exception
     */
    protected static function execHyy() {
        t('HYYPHP_EXEC_TIME'); // 计时Start

        // 实例化控制器
        $ctrl = Loader::controller(CONTROLLER_NAME);

        // 执行方法
        if (method_exists($ctrl, FUNCTION_NAME)) {
            $m = FUNCTION_NAME;
            t('HYYPHP_METHOD_TIME');
            $ctrl -> $m();
            t('HYYPHP_METHOD_TIME', 0);
        } else {
            throw new \Exception(FUNCTION_NAME.' 方法不存在！');
        }

        t('HYYPHP_EXEC_TIME', 0); // 计时End
    }

}