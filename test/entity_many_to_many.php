<?php
require_once __DIR__ . '/boot.php';

$books = [
	(new \Test\Model\Book)->setName('哈利波特'),
	(new \Test\Model\Book)->setName('Javascript从入门到放弃'),
	(new \Test\Model\Book)->setName('PHP大全'),
];

/*
$user = new \Test\Model\Entity;
$user->setBooks($books)
	->setName('hackyoung')
	->setAge(19)
	->setCreated(new \Datetime)
	->save();
 */

$user = \Test\Model\Entity::findOrFail('b77757b4-8317-02ce-70b1-0c100e163cbf');
var_dump($user->getName());
$books = $user->getBooks();
foreach($books as $book) {
	var_dump($book->getName());
}
