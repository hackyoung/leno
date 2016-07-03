<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class Book extends Entity
{
    public static $table = 'book_test';

    public static $attributes = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => [
            'max_length' => 64
        ], 'default' => 'the default'],
        'author_id' => ['type' => 'uuid'],
    ];

    public static $foreign = [
        'author' => [
            'entity' => '\\Test\\Model\\User',
            'local_key' => 'author_id',
            'foreign_key' => 'id'
        ]
    ];

    public static $primary = 'id';
}
