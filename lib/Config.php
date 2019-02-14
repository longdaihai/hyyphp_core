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

class Config {

    private static $str_dir         = CONF_PATH;
    private static $arr_cacheConfig = [];//缓存文件

    private static function parse_config($str_dir) {
        $arr_dir = explode('/', CONF_PATH);
        return implode('/', $arr_dir);
    }

    /**
     * 获取配置文件中 对于KEY的值
     * @param $str_key  建
     * @param $str_file 配置文件名称
     * @return mixed 返回值对于的VALUE
     * @throws \Exception
     */
    public static function get($str_file, $str_key) {
        if(!isset(self::$arr_cacheConfig[$str_file])) {//判断否在缓存中
            $str_filePath = self::parse_config(self::$str_dir) . $str_file . '.php';
            if(is_file($str_filePath)) {//判断文件是否存在
                $arr_config = include $str_filePath;//加载配置文件

                /*************** 与系统config配置合并 ***************/
                if($str_file == 'config') {
                    $path = HYY_PATH . 'config/config.php';
                    if(file_exists($path)) {
                        $conf = include $path;
                    } else {
                        throw new \Exception($path . "惯例配置文件不存在！");
                    }
                    $arr_config = array_merge($conf, $arr_config);
                }
                /*************** 与系统config配置合并 End ***************/

                self::$arr_cacheConfig[$str_file] = $arr_config;
                if(isset($arr_config[$str_key])) {//判断KEY 是否存在
                    return $arr_config[$str_key];
                } else {
                    throw new \Exception('配置项不存在:' . $str_key);
                }

            } else {
                throw  new \Exception('配置文件不存在:' . $str_filePath);
            }
        }
        return self::$arr_cacheConfig[$str_file][$str_key];

    }

    /**
     * 获取配置文件中全部配置参数
     * @param $str_file //配置文件名称
     * @return mixed
     * @throws \Exception
     */
    public static function getAll($str_file) {
        if(!isset(self::$arr_cacheConfig[$str_file])) {//判断否在缓存中
            $str_filePath = self::parse_config(self::$str_dir) . '/' . $str_file . '.php';
            if(is_file($str_filePath)) {//判断文件是否存在
                $arr_config = include $str_filePath; //加载配置文件
                /*************** 与系统config配置合并 ***************/
                if($str_file == 'config') {
                    $path = HYY_PATH . 'config/config.php';
                    if(file_exists($path)) {
                        $conf = include $path;
                    } else {
                        throw new \Exception($path . "惯例配置文件不存在！");
                    }
                    $arr_config = array_merge($conf, $arr_config);
                }
                /*************** 与系统config配置合并 End ***************/
                self::$arr_cacheConfig[$str_file] = $arr_config;
                return $arr_config;
            } else {
                throw  new \Exception('配置文件不存在:' . $str_filePath);
            }
        }
        return self::$arr_cacheConfig[$str_file];
    }

    /**
     * 私有化构造方法
     * Config constructor.
     */
    private function __construct() {
    }

    /**
     * 私有化克隆方法
     */
    private function __clone() {
    }

}