<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

use \Leno\ORM\Exception\EntityNotFoundException;
use \Test\Model\User;
use \Test\Model\Book;

/*
$start = microtime(true);
$user = (new User)->setName('hackyoung');
$book_array = [
    (new Book)->setName('php')
        ->setAuthor($user)
        ->setPublished('2014-05-05 00:00:00')
        ->save(),
    (new Book)->setName('css')
        ->setAuthor($user)
        ->setPublished('2014-05-06 00:00:00')
        ->save(),
    (new Book)->setName('hello world')
        ->setAuthor($user)
        ->setPublished('2014-05-07 00:00:00')
        ->save(),
    (new Book)->setName('world hello')
        ->setAuthor($user)
        ->setPublished('2014-05-08 00:00:00')
        ->save(),
];

foreach ($book_array as $book) {
    $user->addBook($book);
}

$user->save();
echo "使用时间：".((microtime(true) - $start)*1000)."Ms\n";

$user = new \Test\Model\User;
$book = new \Test\Model\Book;
$book->setName('Javascript 从入门到放弃')
    ->setAuthor($user);

$user->setName('hello')
    ->setAge(14);

var_dump($user);
$book->save();
 */

$start = microtime(true);
// $users = User::selector()
//     ->byNameNotLike('hack')
//     ->find();
// foreach ($users as $user) {
//     var_dump($user->toArray());
// }

$user = User::findOrFail('e8c8fa8d-e647-a8bc-2147-883db23f6ef5');

echo json_encode($user->getBook());

// (clone $user)->setName('hello')->save();

// $books = $user->getBook(function($selector) {
//     return $selector->orderPublished('DESC');
// });
// 
// foreach ($books as $book) {
//     var_dump($book->toArray());
// }

echo "使用时间：".((microtime(true) - $start)*1000)."Ms\n";

/*
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
