<?php
define('ROOT', __DIR__);

require_once dirname(__DIR__) . '/vendor/autoload.php';
\Leno\Configure::init();

if(TEST_MVC ?? false) {
    \Test\Worker::setRouterClass('\\Test\\Router');
    $worker = \Test\Worker::instance();
    $worker->execute();
    $worker->logger()->info((string)$worker->getResponse()->getBody());
}
