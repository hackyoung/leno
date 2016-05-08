<?php
define('ROOT', __DIR__);

!defined('TEST_MVC') ?? define('TEST_MVC', false);
require_once dirname(__DIR__) . '/vendor/autoload.php';

\Leno\Configure::init();
if(TEST_MVC) {
    \Test\Worker::setRouterClass('\\Test\\Router');
    $worker = \Test\Worker::instance();
    $worker->execute();
    $worker->logger()->info((string)$worker->getResponse()->getBody());
}
