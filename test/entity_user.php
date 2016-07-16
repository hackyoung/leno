<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

use \Leno\ORM\Exception\EntityNotFoundException;
use \Test\Model\User;
use \Test\Model\Book;
$start = microtime(true);
$user = (new User)->setName('hackyoung');
$user->addBook((new Book)->setName('php_1')->setAuthor($user)->save());
$user->addBook((new Book)->setName('css_1')->setAuthor($user)->save());

$user->save();
echo "使用时间：".((microtime(true) - $start)*1000)."Ms\n";

/*
$user = new \Test\Model\User;
$book = new \Test\Model\Book;
$book->setName('Javascript 从入门到放弃')
    ->setAuthor($user);

$user->setName('hello')
    ->setAge(14);

var_dump($user);
$book->save();
$start = microtime(true);
$user = User::findOrFail('c9250431-fd0c-6511-1d42-f0af24b2b367');
foreach($user->getBook() as $book) {
    $author = $book->getAuthor()->toArray();
    var_dump($author);
    break;
}
echo "使用时间：".((microtime(true) - $start)*1000)."Ms\n";
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
