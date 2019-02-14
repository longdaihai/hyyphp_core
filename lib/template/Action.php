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

namespace hyyphp\template;

class Action {
    //模板编译文件目录
    public $runtime_dir = RUNTIME_PATH;
    //模板文件
    public $template_dir = RUNTIME_PATH . 'templates';
    //编译文件
    public $compile_dir = RUNTIME_PATH . 'templates_c';
    //编译文件
    public $cache_dir = RUNTIME_PATH . 'cache';
    //模板变量
    public $tpl_var = [];
    //是否开启缓存
    public $caching = false;

    public function Action($action_class, $action_function) {

    }

    public function __construct() {
        $this->checkDir();
    }

    //检查目录是否建好
    private function checkDir() {
        if(!file_exists($this->cache_dir)) {
            @mkdir($this->cache_dir, 0777);
            @chmod($this->cache_dir, 0777);
        }
        if(!file_exists($this->template_dir)) {
            @mkdir($this->template_dir, 0777);
            // exit('模板文件目录templates不存在！请手动创建');
        }
        if(!file_exists($this->compile_dir)) {
            @mkdir($this->compile_dir, 0777);
            // exit('编译文件目录templates_c不存在！请手工创建！');
        }

    }

    //模板变量注入方法
    public function assign($tpl_var, $var) {
        // echo $tpl_var; die();
        $this->tpl_var[$tpl_var] = $var;
    }

    //文件编译
    public function display($file) {
        t('HYYPHP_COMPILE_TIME');
        //模板文件地址
        $tpl_file = APP_PATH . 'view/' . $file . '.html';
        $tpl_file = str_replace('\\', '/', $tpl_file);

        if(!file_exists($tpl_file)) {
            exit($tpl_file . ' 模板文件不存在！');
        }
        //编译文件
        $parse_file = $this->compile_dir . '/' . md5($file) . '.php';

        //只有当编译文件不存在或者是模板文件被修改过了
        //才重新编译文件
        if(!file_exists($parse_file) || filemtime($parse_file) < filemtime($tpl_file)) {
            $compile = new Compile($tpl_file);
            $compile->parse($parse_file);
        }

        t('HYYPHP_COMPILE_TIME', 0);

        //开启了缓存才加载缓存文件，否则直接加载编译文件
        if($this->caching) {
            if(!file_exists($this->cache_dir)) {
                mkdir($this->cache_dir, 0777);
            }
            //缓存文件
            $cache_file = $this->cache_dir . '/' . md5($file) . '.html';
            //只有当缓存文件不存在，或者编译文件已被修改过
            //重新生成缓存文件
            if(!file_exists($cache_file) || filemtime($cache_file) < filemtime($parse_file)) {
                //引入缓存文件
                include $parse_file;
                //缓存内容
                $content = ob_get_clean();
                //生成缓存文件
                if(!file_put_contents($cache_file, $content)) {
                    exit('缓存文件生成出错！');
                }
            }
            //载入缓存文件
            include $cache_file;
        } else {
            //载入编译文件
            include $parse_file;
        }
    }


}