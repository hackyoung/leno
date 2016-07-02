<?php
define('TEST_MVC', false);

require __DIR__ . '/boot.php';

use \Leno\Shell\Build;

$build = new Build();
$build->setArg('entitydir', ROOT . '/Model')
    ->setArg('namespace', '\\Test\\Model')
    ->db();
