<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

use \Leno\ORM\Exception\EntityNotFoundException;

$user = new \Test\Model\User;
var_dump($user);

$user->setName('hello')
    ->setAge(14)
    ->save();
/*
try {
    $user = \Test\Model\User::findOrFail('2a3c4c5f-e6d1-b9e8-87ff-d09bf891d200');
} catch (EntityNotFoundException $e) {
    var_dump($e->getMessage());
}
 */
var_dump($user->toArray());
//$user->remove();

/*
var_dump(\Test\Model\User::selector()
    ->limit(0, 10)
    ->execute()->fetchAll());

var_dump(\Test\Model\User::selector()->count());

var_dump($user);
$user->setName('young')
    ->save();
 */
