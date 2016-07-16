<?php
define('TEST_MVC', false);

require __DIR__ . '/boot.php';

use \Leno\Database\Adapter;

var_dump(Adapter::get()->describeIndexes('user_book'));
