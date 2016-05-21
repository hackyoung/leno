<?php
// 定义ROOT变量
define('ROOT', __DIR__);

require_once ROOT .'/vendor/autoload.php';

// 注册名字空间
\Leno\AutoLoader::register('Model', '/model');
\Leno\AutoLoader::register('Controller', '/controller');

// 添加view目录
\Leno\View::addViewDir(ROOT . '/view');

// 设置view的缓存目录
\Leno\View\Template::setCacheDir(ROOT . '/tmp/view');

// 设置router
Worker::setRouterClass('\\Router');

$worker = Worker::instance();

// 将所有的错误转换为异常,如果不需要开启此功能则注释掉这行代码
$worker->errorToException();

// 开始执行worker
$worker->execute();
