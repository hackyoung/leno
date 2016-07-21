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
        'published' => ['type' => 'datetime'],
        'author_id' => ['type' => 'uuid'],
    ];

    public static $unique = [
        'book_name' => ['name']
    ];

    public static $foreign = [
        'author' => [
            'entity' => '\\Test\\Model\\Author',
            'local_key' => 'author_id',
            'foreign_key' => 'id'
        ]
    ];

    public static $primary = 'id';
}
