<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';

//\Leno\Service::setBase('test.service');
\Test\Service::getService('user.name');
