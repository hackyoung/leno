<?php
define('ROOT', __DIR__);

require_once ROOT .'/vendor/autoload.php';

\Leno\AutoLoader::register('Model', '/model');
\Leno\AutoLoader::register('Controller', '/controller');

\Leno\View::addViewDir(ROOT . '/view');

\Leno\View\Template::setCacheDir(ROOT . '/tmp/view');

Worker::setRouterClass('\\Router');

$worker = Worker::instance()->errorToException();
$worker->execute();
