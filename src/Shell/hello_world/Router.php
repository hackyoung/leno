<?php
/**
 * 通过leno init自动生成, 如果需要在路由之前执行逻辑，则重写Router的beforeRoute方法
 */
class Router extends \Leno\Routing\Router
{
    
   //如果需要编写规则，则取消注释下面的代码
   // 
   //protected $rules = [
   //    '^admin' => '\\Admin\\Router', // 将所有以admin开头的路由到Admin\Router
   //    '^blog/{$1}' => 'blog/{$1}', // 将带有一个ID的blog都路由到blog类,并将ID作为其调用参数
   //    '^user/{$1}/blog/{$2}' => 'user/blog/{$1}/{$2}'
   //];
   //
   //如果需要在route之前执行逻辑，则取消注释这个方法然后写你的逻辑
   //
   //protected function beforeRoute()
   //{
   //
   //}
   //
   //
   //如果需要在route之偶执行逻辑，则取消注释这个方法然后实现逻辑
   //
   //protected function afterRoute()
   //{
   //}
}
