<?php
define ('TEST_MVC', false);

require __DIR__ . '/boot.php';

use Leno\Database\Row;
use Test\Model\Author;
use Test\Model\Book;


Row::beginTransaction();
    Row::beginTransaction();   
        Row::beginTransaction();
        Row::commitTransaction();
    Row::commitTransaction();
Row::rollback();

// $author = new Author;
// $author->setMulti([
//     'name' => 'young',
//     'created' => new \Datetime
// ]);
