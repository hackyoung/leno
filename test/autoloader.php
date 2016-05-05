<?php
define('ROOT', dirname(__DIR__));

require_once ROOT . '/src/AutoLoader.php';

\Leno\AutoLoader::register('Leno', '/src');

\Leno\AutoLoader::instance()->execute();

$http = new \Leno\Http;
