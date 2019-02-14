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

/**
 * Class Log
 * @method void log($msg) static 记录一般日志
 * @method void error($msg) static 记录错误日志
 * @method void info($msg) static 记录一般信息日志
 * @method void sql($msg) static 记录 SQL 查询日志
 * @method void notice($msg) static 记录提示日志
 * @method void alert($msg) static 记录报警日志
 */
class Log
{
    const LOG    = 'log';
    const ERROR  = 'error';
    const INFO   = 'info';
    const SQL    = 'sql';
    const NOTICE = 'notice';
    const ALERT  = 'alert';
    const DEBUG  = 'debug';

    /**
     * @var array 日志信息
     */
    protected static $log = [];

    /**
     * @var array 配置参数
     */
    protected static $config = [];

    /**
     * @var array 日志类型
     */
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];

    /**
     * @var log\driver\File|log\driver\Test|log\driver\Socket 日志写入驱动
     */
    protected static $driver;

    /**
     * @var string 当前日志授权 key
     */
    protected static $key;

    /**
     * trace 信息
     * @var array
     */
    protected static $trace = array();

    /**
     * 日志初始化
     * @access public
     * @param  array $config 配置参数
     * @return void
     */
    public static function init($config = [])
    {
        $type  = isset($config['type']) ? $config['type'] : 'File';
        $class = false !== strpos($type, '\\') ? $type : '\\hyyphp\\log\\driver\\' . ucwords($type);

        self::$config = $config;
        unset($config['type']);

        if (class_exists($class)) {
            self::$driver = new $class($config);
        } else {
            throw new \Exception('class not exists:' . $class);
        }

        // 记录初始化信息
        Log::trace('DEBUG' ,'[ INFO ] 记录初始化日志成功：' . $type);
    }

    /**
     * 获取日志信息
     * @access public
     * @param  string $type 信息类型
     * @return array|string
     */
    public static function getLog($type = '')
    {
        return $type ? self::$log[$type] : self::$log;
    }

    /**
     * 记录调试信息
     * @access public
     * @param  mixed  $msg  调试信息
     * @param  string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'log')
    {
        self::$log[$type][] = $msg;

        // 命令行下面日志写入改进
        IS_CLI && self::save();
    }

    /**
     * 清空日志信息
     * @access public
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 设置当前日志记录的授权 key
     * @access public
     * @param  string $key 授权 key
     * @return void
     */
    public static function key($key)
    {
        self::$key = $key;
    }

    /**
     * 检查日志写入权限
     * @access public
     * @param  array $config 当前日志配置参数
     * @return bool
     */
    public static function check($config)
    {
        return !self::$key || empty($config['allow_key']) || in_array(self::$key, $config['allow_key']);
    }

    /**
     * 保存调试信息
     * @access public
     * @return bool
     */
    public static function save()
    {
        // 没有需要保存的记录则直接返回
        if (empty(self::$log)) {
            return true;
        }

        is_null(self::$driver) && self::init(Config::get('config', 'log'));

        // 检测日志写入权限
        if (!self::check(self::$config)) {
            return false;
        }

        if (empty(self::$config['level'])) {
            // 获取全部日志
            $log = self::$log;
            // if (!App::$debug && isset($log['debug'])) {
            //     unset($log['debug']);
            // }
        } else {
            // 记录允许级别
            $log = [];
            foreach (self::$config['level'] as $level) {
                if (isset(self::$log[$level])) {
                    $log[$level] = self::$log[$level];
                }
            }
        }

        if ($result = self::$driver->save($log, true)) {
            self::$log = [];
        }

        Hook::listen('log_write_done', $log);

        return $result;
    }

    /**
     * 实时写入日志信息 并支持行为
     * @access public
     * @param  mixed  $msg   调试信息
     * @param  string $type  信息类型
     * @param  bool   $force 是否强制写入
     * @return bool
     */
    public static function write($msg, $type = 'log', $force = false)
    {
        $log = self::$log;

        // 如果不是强制写入，而且信息类型不在可记录的类别中则直接返回 false 不做记录
        if (true !== $force && !empty(self::$config['level']) && !in_array($type, self::$config['level'])) {
            return false;
        }

        // 封装日志信息
        $log[$type][] = $msg;

        // 监听 log_write
        // Hook::listen('log_write', $log);

        is_null(self::$driver) && self::init(Config::get('config', 'log'));

        // 写入日志
        if ($result = self::$driver->save($log, false)) {
            self::$log = [];
        }

        return $result;
    }

    /**
     * 静态方法调用
     * @access public
     * @param  string $method 调用方法
     * @param  mixed  $args   参数
     * @return void
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);

            call_user_func_array('\\hyyphp\\lib\\Log::record', $args);
        }
    }

    /**
     * 请求结束,由框架保存显示Trace
     * @return null
     */
    public static function show() {
        $trace_tmp = self::$trace;
        $files     = get_included_files();
        foreach ($files as $key => $file) {
            $files[$key] = $file . ' ( ' . number_format(filesize($file) / 1024, 2) . ' KB )';
        }
        $cltime = t('HYYPHP_TIME', -1);
        $trace_tmp['SYS'] = array(
            "请求信息"  => $_SERVER['REQUEST_METHOD'] . ' ' . strip_tags($_SERVER['REQUEST_URI']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            "总吞吐量"  => number_format(1 / $cltime, 2) . ' req/s',
            "总共时间"  => number_format($cltime, 5) . ' s',
            "框架加载"  => number_format(($cltime - t('HYYPHP_EXEC_TIME', -1)), 5) . ' s (route:' . number_format(t('HYYPHP_ROUTE_TIME', -1), 6) . 's application:' . number_format(t('HYYPHP_EXEC_TIME', -1), 6) . 's)',
            "Method" => number_format(t('HYYPHP_METHOD_TIME', -1), 5) . ' s',
            "内存使用"  => number_format((memory_get_usage()-HYYPHP_START_MEM) / 1024 / 1024, 5) . ' MB',
            '文件加载'  => count($files),
            '会话信息'  => 'SESSION_ID=' . session_id(),
        );

        $trace_tmp['FILE'] = $files;

        $arr = array(
            'SYS'   => '基本',
            'FILE'  => '文件',
//            'ERR'   => '错误',
            'SQL'   => '数据库',
            'DEBUG' => '调试',
        );
        foreach ($arr as $key => $value) {
            $num = 50;
            $len = 0;
            if (@is_array($trace_tmp[$key]) && ($len = count($trace_tmp[$key])) > $num) {
                $trace_tmp[$key] = array_slice($trace_tmp[$key], 0, $num);
            }
            $trace[$value] = @$trace_tmp[$key];
            if ($len > $num) {
                $trace[$value][] = "...... 共 $len 条";
            }

        }
        $totalTime = number_format($cltime, 3);
        include LIB_PATH . 'tpl/trace.php';
    }

    /**
     * 日志追踪
     * @param string $key 键
     * @param string $value 值
     * @return null
     */
    public static function trace($key, $value) {
        if (!Config::get('config', 'debug_trace')) {
            return;
        }

        if (isset(self::$trace[$key]) && count(self::$trace[$key]) > 50) {
            return;
        }

        self::$trace[$key][] = $value;
    }

}
