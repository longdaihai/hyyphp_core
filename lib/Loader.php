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

class Loader {

    /**
     * @var array 已加载的类
     */
    public static $classMap = [];

    /**
     * @var array 命名空间别名
     */
    protected static $namespaceAlias = [];

    /**
     * @var array PSR-4 命名空间前缀长度映射
     */
    private static $prefixLengthsPsr4 = [];

    /**
     * @var array PSR-4 的加载目录
     */
    private static $prefixDirsPsr4 = [];

    /**
     * @var array PSR-4 加载失败的回退目录
     */
    private static $fallbackDirsPsr4 = [];

    /**
     * @var array PSR-0 命名空间前缀映射
     */
    private static $prefixesPsr0 = [];

    /**
     * @var array PSR-0 加载失败的回退目录
     */
    private static $fallbackDirsPsr0 = [];

    /**
     * @var array 需要加载的文件
     */
    private static $files = [];

    public static function autoload($className) {
        // 检测命名空间别名
        if(!empty(self::$namespaceAlias)) {
            $namespace = dirname($className);
            if(isset(self::$namespaceAlias[$namespace])) {
                $original = self::$namespaceAlias[$namespace] . '\\' . basename($className);
                if(class_exists($original)) {
                    return class_alias($original, $className, false);
                }
            }
        }

        // 如果以加载过无需重复加载
        if(!empty(self::$classMap[$className])) {
            return true;
        }

        // 查找 PSR-4
        $logicalPathPsr4 = strtr($className, '\\', DS) . EXT;
        $first = $className[0];

        if(isset(self::$prefixLengthsPsr4[$first])) {
            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
                if(0 === strpos($className, $prefix)) {
                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
                        if(is_file($file = $dir . DS . substr($logicalPathPsr4, $length))) {
                            include $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-4 fallback dirs
        foreach (self::$fallbackDirsPsr4 as $dir) {
            if(is_file($file = $dir . DS . $logicalPathPsr4)) {
                include $file;
            }
        }

        if (false !== $pos = strrpos($className, '\\')) {
            // namespace class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DS);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($className, '_', DS) . EXT;
        }

        if (isset(self::$prefixesPsr0[$first])) {
            foreach (self::$prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($className, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-0 fallback dirs
        foreach (self::$fallbackDirsPsr0 as $dir) {
            if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                return $file;
            }
        }

        // 找不到则设置映射为 false 并返回
        return self::$classMap[$className] = false;

    }

    /**
     * 注册自动加载机制
     * @access public
     * @param  callable $autoload 自动加载处理方法
     * @return void
     */
    public static function register($autoload = null) {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: 'hyyphp\\Loader::autoload', true, true);

        // Composer 自动加载支持
        if (is_dir(VENDOR_PATH . 'composer')) {
            if (PHP_VERSION_ID >= 50600 && is_file(VENDOR_PATH . 'composer' . DS . 'autoload_static.php')) {
                require VENDOR_PATH . 'composer' . DS . 'autoload_static.php';

                $declaredClass = get_declared_classes();
                $composerClass = array_pop($declaredClass);

                foreach (['prefixLengthsPsr4', 'prefixDirsPsr4', 'fallbackDirsPsr4', 'prefixesPsr0', 'fallbackDirsPsr0', 'classMap', 'files'] as $attr) {
                    if (property_exists($composerClass, $attr)) {
                        self::${$attr} = $composerClass::${$attr};
                    }
                }
            } else {
                self::registerComposerLoader();
            }
        }

        // 注册命名空间定义
        self::addNamespace([
            'hyyphp' => HYY_PATH . 'lib' . DS,
        ]);

        // 自动加载控制器目录
        self::$fallbackDirsPsr4[] = rtrim(APP_PATH, DS);

        // 自动加载扩展类目录
        self::$fallbackDirsPsr4[] = rtrim(EXTEND_PATH, DS);

    }

    /**
     * 注册命名空间别名
     * @access public
     * @param  array|string $namespace 命名空间
     * @param  string $original        源文件
     * @return void
     */
    public static function addNamespaceAlias($namespace, $original = '') {
        if(is_array($namespace)) {
            self::$namespaceAlias = array_merge(self::$namespaceAlias, $namespace);
        } else {
            self::$namespaceAlias[$namespace] = $original;
        }
    }

    /**
     * 注册命名空间
     * @access public
     * @param  string|array $namespace 命名空间
     * @param  string $path            路径
     * @return void
     */
    public static function addNamespace($namespace, $path = '') {
        if(is_array($namespace)) {
            foreach ($namespace as $prefix => $paths) {
                self::addPsr4($prefix . '\\', rtrim($paths, DS), true);
            }
        } else {
            self::addPsr4($namespace . '\\', rtrim($path, DS), true);
        }
    }

    /**
     * 添加 PSR-4 空间
     * @access private
     * @param  array|string $prefix 空间前缀
     * @param  string $paths        路径
     * @param  bool $prepend        预先设置的优先级更高
     * @return void
     */
    private static function addPsr4($prefix, $paths, $prepend = false) {
        if(!$prefix) {
            self::$fallbackDirsPsr4 = $prepend ?
                array_merge((array)$paths, self::$fallbackDirsPsr4) :
                array_merge(self::$fallbackDirsPsr4, (array)$paths);

        } else if(!isset(self::$prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException(
                    "A non-empty PSR-4 prefix must end with a namespace separator."
                );
            }

            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix] = (array)$paths;

        } else {
            self::$prefixDirsPsr4[$prefix] = $prepend ?
                array_merge((array)$paths, self::$prefixDirsPsr4[$prefix]) :
                array_merge(self::$prefixDirsPsr4[$prefix], (array)$paths);
        }
    }

    /**
     * 注册 classmap
     * @access public
     * @param  string|array $class 类名
     * @param  string $map         映射
     * @return void
     */
    public static function addClassMap($class, $map = '') {
        if(is_array($class)) {
            self::$classMap = array_merge(self::$classMap, $class);
        } else {
            self::$classMap[$class] = $map;
        }
    }

    // 加载composer autofile文件
    public static function loadComposerAutoloadFiles() {
        foreach (self::$files as $fileIdentifier => $file) {
            if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                __require_file($file);

                $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
            }
        }
    }

    /**
     * 注册 composer 自动加载
     * @access private
     * @return void
     */
    private static function registerComposerLoader() {
        if (is_file(VENDOR_PATH . 'composer/autoload_namespaces.php')) {
            $map = require VENDOR_PATH . 'composer/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                self::addPsr0($namespace, $path);
            }
        }

        if (is_file(VENDOR_PATH . 'composer/autoload_psr4.php')) {
            $map = require VENDOR_PATH . 'composer/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                self::addPsr4($namespace, $path);
            }
        }

        if (is_file(VENDOR_PATH . 'composer/autoload_classmap.php')) {
            $classMap = require VENDOR_PATH . 'composer/autoload_classmap.php';
            if ($classMap) {
                self::addClassMap($classMap);
            }
        }

        if (is_file(VENDOR_PATH . 'composer/autoload_files.php')) {
            self::$files = require VENDOR_PATH . 'composer/autoload_files.php';
        }
    }

    /**
     * 添加 PSR-0 命名空间
     * @access private
     * @param  array|string $prefix  空间前缀
     * @param  array        $paths   路径
     * @param  bool         $prepend 预先设置的优先级更高
     * @return void
     */
    private static function addPsr0($prefix, $paths, $prepend = false) {
        if (!$prefix) {
            self::$fallbackDirsPsr0 = $prepend ?
                array_merge((array) $paths, self::$fallbackDirsPsr0) :
                array_merge(self::$fallbackDirsPsr0, (array) $paths);
        } else {
            $first = $prefix[0];

            if (!isset(self::$prefixesPsr0[$first][$prefix])) {
                self::$prefixesPsr0[$first][$prefix] = (array) $paths;
            } else {
                self::$prefixesPsr0[$first][$prefix] = $prepend ?
                    array_merge((array) $paths, self::$prefixesPsr0[$first][$prefix]) :
                    array_merge(self::$prefixesPsr0[$first][$prefix], (array) $paths);
            }
        }
    }

    /**
     * 实例化控制器
     * @param string $class 类名
     * @param string $module 模块名
     * @return class 实例化类
     */
    public static function controller($class, $module = MODULE) {
        $name = "$module\\controller\\$class";
        return self::instance($name);
    }

    /**
     * 实例化类并存储
     * @param string $class 类名
     * @return class 实例化类
     */
    public static function instance($class) {
        static $ins = [];
        if (!isset($ins[$class])) {
            $ins[$class] = new $class;
        }
        return $ins[$class];
    }

}