<?php
// 如果你的服务器环境没有使用rewrite指向到这个文件,那么以
// www.sample.com/index.php/hello/world的形式访问,这个文件
// 是逻辑执行的入口

// 定义ROOT变量
define('ROOT', __DIR__);

require_once ROOT . '/vendor/autoload.php';

// 载入应用需要的名字映射
require_once ROOT . '/autoload.php';

// 添加view目录
\Leno\View::addViewDir('global', ROOT . '/view');

// 设置view的缓存目录
\Leno\View\Template::setCacheDir(ROOT . '/tmp/view');

// 设置router
Worker::setRouterClass('\\Router');

$worker = Worker::instance();

// 将所有的错误转换为异常,如果不需要开启此功能则注释掉这行代码
$worker->errorToException();

// 开始执行worker
$worker->execute();
