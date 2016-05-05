<?php
define('ROOT', __DIR__);

!defined('TEST_MVC') ?? define('TEST_MVC', false);
require_once dirname(__DIR__) . '/vendor/autoload.php';

if(TEST_MVC) {
    \Test\Worker::setRouterClass('\\Test\\Router');
    \Leno\Service::setBase('test.service');
    $worker = \Test\Worker::instance();
    $worker->execute();
    $worker->logger()->info((string)$worker->getResponse()->getBody());
}
