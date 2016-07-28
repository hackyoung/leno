<?php
require __DIR__ . '/boot.php';

use \Leno\Routing\Rule;

$url = (new Rule('/hello/world/1234/4567', [
    '/hello/world/\d+/\d+' => 'user/${1}/${2}'
]))->handle();
