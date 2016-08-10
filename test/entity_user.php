<?php
define ('TEST_MVC', false);
require_once(__DIR__ . '/boot.php');

use \Leno\ORM\Exception\EntityNotFoundException;
use \Test\Model\User;
use \Test\Model\Author;
use \Test\Model\Book;

$start = microtime(true);
// $author = new Author;
// $author->setName('hello world')
//     ->setCreated(new \Datetime)
//     ->save();

$author = Author::find('1bc79ae4-98a9-6d58-732d-6f90cf76f5f1');
var_dump($author->getBookIds());
// $books = Book::selector()->find();
// foreach ($books as $book) {
//     $author->addBook($book);
// }
// $author->save();

// $i = 10;
// Author::selector()->byIdNotNull()->find();
// while($i--) {
//     var_dump(Author::find('479dfc05-7403-f1e2-d69c-b5b6f25a825c'));
// }
// var_dump();
// $author = Author::findOrFail('479dfc05-7403-f1e2-d69c-b5b6f25a825c');
// 
// echo json_encode($author->getBook(function($selector) {
//     return $selector->limit(1);
// }));
// $i = 1000;
// while($i--) {
//     echo "\n";
//     echo json_encode($author->getBook());
// }
// 
// $book = new Book;
// $book->setName('javascript 从入门到放弃')
//      ->setPublished(new \Datetime);
// $author->setBook($book);
// 
// $book = new Book;
// $book->setName('PHP从入门到放弃')
//     ->setPublished(new \Datetime);
// $author->addBook($book);
// 
// $author->save();

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

// $start = microtime(true);
// $user = User::findOrFail('e8c8fa8d-e647-a8bc-2147-883db23f6ef5');
// var_dump(Book::selector()->byAuthor($user)->find());
// 
// $books = Book::selector()->join(User::selector()
//     ->field('name', 'user_name')
//     ->onId(Book::selector()->getFieldExpr('author_id'))
// )->execute()->fetchAll();

// var_dump($books);

// $users = User::selector()
//     ->byNameNotLike('hack')
//     ->find();
// foreach ($users as $user) {
//     var_dump($user->toArray());
// }

// 
// echo json_encode($user->getBook());

// (clone $user)->setName('hello')->save();

// $books = $user->getBook(function($selector) {
//     return $selector->orderPublished('DESC');
// });
// 
// foreach ($books as $book) {
//     var_dump($book->toArray());
// }

// echo "使用时间：".((microtime(true) - $start)*1000)."Ms\n";

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
