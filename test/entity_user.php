<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

use \Leno\ORM\Exception\EntityNotFoundException;
use \Test\Model\User;
use \Test\Model\Book;

$user = (new User)->setName('young');

$user->addBook((new Book)->setName('PHP'));
$user->addBook((new Book)->setName('CSS'));

$user->save();

/*
$user = new \Test\Model\User;
$book = new \Test\Model\Book;
$book->setName('Javascript 从入门到放弃')
    ->setAuthor($user);

$user->setName('hello')
    ->setAge(14);

var_dump($user);

$book->save();
$book = \Test\Model\Book::findOrFail('55a58b9-0cae-622d-a0f1-582f8eaf3918');

var_dump($book->getAuthor()->getName());
$book->setAuthor((new \Test\Model\User)->setName('hello world world'));

var_dump($book->getAuthor()->getName());

$book->save();

//var_dump($user->toArray());
//$user->remove();

var_dump(\Test\Model\User::selector()
    ->limit(0, 10)
    ->execute()->fetchAll());

var_dump(\Test\Model\User::selector()->count());

var_dump($user);
$user->setName('young')
    ->save();
 */
