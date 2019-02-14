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

return [
    //路由模式 1rewrite模式/index/index(不支持二级目录) 2普通模式index.php?c=index&a=index
    'PATH_INFO'             => 2,
    'debug_trace'           => false,     // 页面trace

    'default_controller'    => 'Index',   // 默认控制器
    'default_function'      => 'index',   // 默认方法
    // PATHINFO模式下，各参数之间的分割符号
    'URL_PATHINFO_DEPR'     =>  '/',
    // 输入变量是否自动强制转换为字符串 如果开启则数组变量需要手动传入变量修饰符获取变量
    'VAR_AUTO_STRING'       =>  false,
    'DEFAULT_FILTER'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',

    // 默认时区
    'default_timezone'       => 'PRC',

    // 自定义加载类文件目录
    'loadPath' => [],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache'  => [
        'type'   => 'File',
        'path'   => CACHE_PATH,
        'prefix' => '',
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------
    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'hyyphp',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        'httponly'       => true,
        'secure'         => false,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],
    // 是否开启错误信息保存
    'error_log' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // HTTPS代理标识
    'https_agent_name'       => '',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',

    'http_agent_ip'          => '',

];