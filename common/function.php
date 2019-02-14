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

use hyyphp\Cache;
use hyyphp\Config;
use hyyphp\Cookie;
use hyyphp\Log;
use hyyphp\Session;

if(!function_exists('writeLog')) {
    function writeLog($msg, $type = 'log') {
        Log::trace('DEBUG', '[writeLog] ' . $msg);
        return Log::write($msg, $type);
    }
}

if(!function_exists('http_get')) {
    /**
     * 发起GET请求
     * @param string $url
     * @return string content
     */
    function http_get($url, $timeOut = 5, $connectTimeOut = 5) {
        $oCurl = curl_init();
        if(stripos($url, "http://") !== false || stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        if(intval($aStatus ["http_code"]) == 200) {
            return [
                'status'  => true,
                'content' => $sContent,
                'code'    => $aStatus ["http_code"],
            ];
        } else {
            return [
                'status'  => false,
                'content' => json_encode(["error" => $error, "url" => $url]),
                'code'    => $aStatus ["http_code"],
            ];
        }
    }
}

if(!function_exists('http_post')) {
    /**
     * 发起POST请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    function http_post($url, $param, $timeOut = 5, $connectTimeOut = 5) {
        $oCurl = curl_init();
        if(stripos($url, "http://") !== false || stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if(is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = [];
            foreach ($param as $key => $val) {
                $aPOST [] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
        curl_close($oCurl);
        if(intval($aStatus ["http_code"]) == 200) {
            return [
                'status'  => true,
                'content' => $sContent,
                'code'    => $aStatus ["http_code"],
            ];
        } else {
            return [
                'status'  => false,
                'content' => json_encode(["error" => $error, "url" => $url]),
                'code'    => $aStatus ["http_code"],
            ];
        }
    }
}

if(!function_exists('json')) {
    /**
     * [json description]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    function json($arr = []) {
        header('content-type:application/json;charset=utf8');
        if(!$arr || !is_array($arr)) {
            exit('参数出错！');
        }
        $data = json_encode($arr, JSON_UNESCAPED_UNICODE);
        if(JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
        }
        return $data === null ? [] : $data;
    }
}

if(!function_exists('returnJson')) {
    /**
     * 响应返回JSON 格式数据
     * @param  [type] $arr [description]
     */
    function returnJson($arr, $msg = '', $data = []) {
        header('content-type:application/json;charset=utf8');
        if(!$arr && $arr != 0) {
            exit('参数出错！');
        }
        if(is_array($arr)) {
            $res = json_encode($arr, JSON_UNESCAPED_UNICODE);
            if(JSON_ERROR_NONE !== json_last_error()) {
                throw new RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
            }
            exit($res);
        }
        if(is_numeric($arr)) {
            $res = [
                'code' => $arr,
                'msg'  => $msg,
                'data' => $data,
            ];
            $res = json_encode($res, JSON_UNESCAPED_UNICODE);
            if(JSON_ERROR_NONE !== json_last_error()) {
                throw new RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
            }
            exit($res);
        }
    }
}

if(!function_exists('I')) {
    /**
     * 获取输入参数 支持过滤和默认值
     * 使用方法:
     * <code>
     * I('id',0); 获取id参数 自动判断get或者post
     * I('post.name','','htmlspecialchars'); 获取$_POST['name']
     * I('get.'); 获取$_GET
     * </code>
     * @param string $name   变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter  参数过滤方法
     * @param mixed $datas   要获取的额外数据源
     * @return mixed
     */
    function I($name, $default = '', $filter = null, $datas = null) {
        static $_PUT = null;
        if(strpos($name, '/')) {
            // 指定修饰符
            list($name, $type) = explode('/', $name, 2);
        } else if(Config::get('config', 'VAR_AUTO_STRING')) {
            // 默认强制转换为字符串
            $type = 's';
        }
        if(strpos($name, '.')) {
            // 指定参数来源
            list($method, $name) = explode('.', $name, 2);
        } else {
            // 默认为自动判断
            $method = 'param';
        }
        switch (strtolower($method)) {
            case 'get':
                $input = &$_GET;
                break;
            case 'post':
                $input = &$_POST;
                break;
            case 'put':
                if(is_null($_PUT)) {
                    parse_str(file_get_contents('php://input'), $_PUT);
                }
                $input = $_PUT;
                break;
            case 'param':
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input = $_POST;
                        break;
                    case 'PUT':
                        if(is_null($_PUT)) {
                            parse_str(file_get_contents('php://input'), $_PUT);
                        }
                        $input = $_PUT;
                        break;
                    default:
                        $input = $_GET;
                }
                break;
            case 'path':
                $input = [];
                if(!empty($_SERVER['PATH_INFO'])) {
                    $depr = Config::get('config', 'URL_PATHINFO_DEPR');
                    $input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
                }
                break;
            case 'request':
                $input = &$_REQUEST;
                break;
            case 'session':
                $input = &$_SESSION;
                break;
            case 'cookie':
                $input = &$_COOKIE;
                break;
            case 'server':
                $input = &$_SERVER;
                break;
            case 'globals':
                $input = &$GLOBALS;
                break;
            case 'data':
                $input = &$datas;
                break;
            default:
                return null;
        }
        if('' == $name) {
            // 获取全部变量
            $data = $input;
            $filters = isset($filter) ? $filter : Config::get('config', 'DEFAULT_FILTER');
            if($filters) {
                if(is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = array_map_recursive($filter, $data); // 参数过滤
                }
            }
        } else if(isset($input[$name])) {
            // 取值操作
            $data = $input[$name];
            $filters = isset($filter) ? $filter : Config::get('config', 'DEFAULT_FILTER');
            if($filters) {
                if(is_string($filters)) {
                    if(0 === strpos($filters, '/')) {
                        if(1 !== preg_match($filters, (string)$data)) {
                            // 支持正则验证
                            return isset($default) ? $default : null;
                        }
                    } else {
                        $filters = explode(',', $filters);
                    }
                } else if(is_int($filters)) {
                    $filters = [$filters];
                }

                if(is_array($filters)) {
                    foreach ($filters as $filter) {
                        $filter = trim($filter);
                        if(function_exists($filter)) {
                            $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                        } else {
                            $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                            if(false === $data) {
                                return isset($default) ? $default : null;
                            }
                        }
                    }
                }
            }
            if(!empty($type)) {
                switch (strtolower($type)) {
                    case 'a': // 数组
                        $data = (array)$data;
                        break;
                    case 'd': // 数字
                        $data = (int)$data;
                        break;
                    case 'f': // 浮点
                        $data = (float)$data;
                        break;
                    case 'b': // 布尔
                        $data = (boolean)$data;
                        break;
                    case 's': // 字符串
                    default:
                        $data = (string)$data;
                }
            }
        } else {
            // 变量默认值
            $data = isset($default) ? $default : null;
        }
        is_array($data) && array_walk_recursive($data, 'think_filter');
        return $data;
    }
}

if(!function_exists('C')) {
    /**
     * [C 获取配置文件]
     * @param string $file [文件名]
     * @param string $name [配置名称]
     */
    function C($file = '', $name = '') {
        if($file == '') {
            exit('C 配置文件不能为空!');
        }
        if($name == '') {
            return Config::getAll($file);
        } else {
            return Config::get($file, $name);
        }
    }
}

if(!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $var      变量
     * @param boolean $echo   是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label   标签 默认为空
     * @param boolean $strict 是否严谨 默认为true
     * @return void|string
     */
    function dump($var, $echo = true, $label = null, $strict = true) {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if(!$strict) {
            if(ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if(!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if($echo) {
            echo($output);
            return null;
        } else
            return $output;
    }
}

if(!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed $name    缓存名称，如果为数组表示进行缓存设置
     * @param mixed $value   缓存值
     * @param mixed $options 缓存参数
     * @param string $tag    缓存标签
     * @return mixed
     */
    function cache($name, $value = '', $options = null, $tag = null) {
        if(is_array($options)) {
            // 缓存操作的同时初始化
            $cache = Cache::connect($options);
        } else if(is_array($name)) {
            // 缓存初始化
            return Cache::connect($name);
        } else {
            $cache = Cache::init();
        }

        if(is_null($name)) {
            return $cache->clear($value);
        } else if('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
        } else if(is_null($value)) {
            // 删除缓存
            return $cache->rm($name);
        } else if(0 === strpos($name, '?') && '' !== $value) {
            $expire = is_numeric($options) ? $options : null;
            return $cache->remember(substr($name, 1), $value, $expire);
        } else {
            // 缓存数据
            if(is_array($options)) {
                $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
            } else {
                $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
            }
            if(is_null($tag)) {
                return $cache->set($name, $value, $expire);
            } else {
                return $cache->tag($tag)->set($name, $value, $expire);
            }
        }
    }
}

if(!function_exists('session')) {
    /**
     * Session管理
     * @param string|array $name session名称，如果为数组表示进行session设置
     * @param mixed $value       session值
     * @param string $prefix     前缀
     * @return mixed
     */
    function session($name, $value = '', $prefix = null) {
        if(is_array($name)) {
            // 初始化
            Session::init($name);
        } else if(is_null($name)) {
            // 清除
            Session::clear('' === $value ? null : $value);
        } else if('' === $value) {
            // 判断或获取
            return 0 === strpos($name, '?') ? Session::has(substr($name, 1), $prefix) : Session::get($name, $prefix);
        } else if(is_null($value)) {
            // 删除
            return Session::delete($name, $prefix);
        } else {
            // 设置
            return Session::set($name, $value, $prefix);
        }
    }
}

if(!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string|array $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed $value       cookie值
     * @param mixed $option      参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null) {
        if(is_array($name)) {
            // 初始化
            Cookie::init($name);
        } else if(is_null($name)) {
            // 清除
            Cookie::clear($value);
        } else if('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? Cookie::has(substr($name, 1), $option) : Cookie::get($name, $option);
        } else if(is_null($value)) {
            // 删除
            return Cookie::delete($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}

if(!function_exists('isAjax')) {
    /**
     * 检查是否是AJAX请求。
     * Check is ajax request.
     * @static
     * @access public
     * @return bool
     */
    function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true;
        if(isset($_GET['HTTP_X_REQUESTED_WITH']) && $_GET['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true;
        return false;
    }
}

/**
 * 计时函数
 * @param  string $key 计时的标记
 * @param  string $end 是否结束 -1 返回当前key的第一次记录的时间
 *                              1 返回上次key到现在的时间（也就是执行时间）
 *                              0 保存key第一次到现在的时间差
 * @param  int $settime 为key设置的时间
 * @return int/void
 */
function t($key, $end = '', $settime = null) {
    static $time = array(); // 计时
    if (empty($key)) {
        return $time;
    }

    if (!is_null($settime)) {
        $time[$key] = $settime;
        return;
    }

    if ($end === -1) {
        return $time[$key]; // 返回 key
    } elseif ($end === 1) {
        return microtime(1) - $time[$key]; // 返回 上次key到现在的时间
    } elseif ($end === 0) {
        $time[$key] = microtime(1) - $time[$key]; // 记录 上次key现在的时间
    } elseif (!empty($end)) {
        if (!isset($time[$end])) {
            $time[$end] = microtime(1);
        }

        return $time[$end] - $time[$key]; // 返回 两个key的差值
    } else {
        $time[$key] = microtime(1); // 记录 当前key
    }
}
