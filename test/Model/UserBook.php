<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class UserBook extends Entity
{
    public static $attributes = [
        'user_book_id' => ['type' => 'uuid'],
        'author_id' => ['type' => 'uuid'],
        'book_id' => ['type' => 'uuid']
    ];

    public static $primary = 'user_book_id';

    public static $table = 'user_book';
}
