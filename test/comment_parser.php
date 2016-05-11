<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';

$test = 
<<<EOF
/**
 * @name test
 * @description hello world
 * @param leno int the leno description
 */
EOF;

$parser = new \Leno\Doc\CommentParser($test);
var_dump($parser->getDescription());
var_dump($parser->getParam());
var_dump($parser->getTags());
