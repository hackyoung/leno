<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class Book extends Entity
{
    public static $table = 'book_test';

    public static $attributes = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'null' => false, 'extra' => [
            'max_length' => 64
        ], 'default' => 'the default'],
        'author_id' => ['type' => 'uuid'],
    ];

    public static $foreign = [
        'book' => [
            'entity' => '\\Test\\Model\\Book',
            'local' => 'id',
            'foreign' => 'author_id'
        ]
    ];

    public static $primary = 'id';
}
