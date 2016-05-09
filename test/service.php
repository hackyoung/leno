<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';

\Leno\Service::register('test', 'test.service');
$response = \Leno\Service::getService('test.hello')->execute();
