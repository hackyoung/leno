<?php
define('ROOT', __DIR__);

require_once ROOT .'/vendor/autoload.php';

\Leno\View::addViewDir(ROOT . '/view');

Worker::setRouterClass('Router');

$worker = Worker::instance();
$worker->execute();
