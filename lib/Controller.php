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

class Controller {

     public function __construct() {

     }

     public function assign($tpl_var, $var) {
          if($tpl_var && $var) {
               $tpl = new \hyyphp\template\Action();
               $tpl->assign($tpl_var,$var);
          }else {
               exit('模板变量名没有设置好');
          }
     }

     public function fetch($file) {
          if($file) {
               $tpl = new \hyyphp\template\Action();
               $tpl->display($file);
          }else {
               exit('模板文件为空！');
          }

     }
}