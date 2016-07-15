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

    public static $unique = [
        'user_book' => ['author_id', 'book_id']
    ];

    public static $foreign = [
        'user' => [
            'local_key' => 'author_id',
            'foreign_key' => 'id',
            'entity' => '\\Test\\Model\\User'
        ],
        'book' => [
            'local_key' => 'book_id',
            'foreign_key' => 'id',
            'entity' => '\\Test\\Model\\Book'
        ]
    ];
}
