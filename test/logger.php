<?php
require_once __DIR__ . '/boot.php';

$worker->logger()->info('hello world', ['hello' => 'world']);

