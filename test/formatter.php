<?php
define('TEST_MVC', false);

require __DIR__ . '/boot.php';

$formatter = new \Leno\Console\Formatter;

$formatter->setInput('<keyword>fakjfa</keyword><info>hfjaklkfa</info>fjalfkal')->format();
