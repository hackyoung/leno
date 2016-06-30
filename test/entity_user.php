<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

$user = new \Test\Model\User;
/*
$user->setName('hello')
    ->setAge(14)
    ->save();
 */

$user = \Test\Model\User::find('8d17b093-69e6-f7c2-9dd1-d7c8863a1e49');
$user->setName('young')
    ->save();
