<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';

\Leno\Service::register('test', 'test.service');
$service = \Leno\Service::getService('test.hello');

$service->setParam('hello world')->execute();
