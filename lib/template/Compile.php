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

class Compile {
    //模板内容
    private $content = '';

    public function __construct($tpl_file) {
        $this->content = file_get_contents($tpl_file);
    }

    //模板编译
    public function parse($parse_file) {
        //调用普通变量解析器
        $this->parseVar();
        $this->parseForeach();

        //编译完成后，生成编译文件
        if (!file_put_contents($parse_file, $this->content)) {
            exit('编译文件生成出错！');
        }
    }

    //解析普通变量，如把{$name}解析成$this->_tpl_var['name']
    public function parseVar() {
        $pattern = '/\{\$([\w\d]+)\}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, '<?php echo \$this->tpl_var["$1"]; ?>', $this->content);
        }
    }

    // foreach
    private function parseForeach() {
        $pattForeach = '/\{foreach\s+\$([\w]+)\(([\w]+),([\w]+)\)\}/';
        $pattEnd = '/\{\/foreach\}/';
        $pattVal = '/\{\@([\w]+)\}/';
        $pattValPrm = '/\{\@([\w]+)\[\'([\w]+)\'\]\}/';
        if(preg_match($pattForeach, $this->content)) {
            if(preg_match($pattEnd, $this->content)) {
                $this->content = preg_replace($pattForeach, '<?php foreach(\$this->tpl_var["$1"] as \$$2=>\$$3) { ?>', $this->content);
                $this->content = preg_replace($pattEnd, '<?php } ?>', $this->content);
                if(preg_match($pattVal, $this->content)) {
                    $this->content = preg_replace($pattVal, '<?php echo \$$1; ?>', $this->content);
                }
                if(preg_match($pattValPrm, $this->content)) {
                    $this->content = preg_replace($pattValPrm, '<?php echo \$$1[\'$2\']; ?>', $this->content);
                }
            }else {
                exit('Error:没有{/foreach}结束标签。');
            }
        }
    }

    //if
    private function parseIf() {
        $pattIf = '/\{if(([\w]+))\}/';
        if(preg_match($pattIf, $this->content)) {
            exit('找到if开始');
        }
    }


}