<?php
define('TEST_MVC', false);

require __DIR__ . '/boot.php';

use \Leno\Database\Adapter;

var_dump(Adapter::get()->describeForeignKeys('book_test'));
