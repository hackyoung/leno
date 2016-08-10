<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class Book extends Entity
{
    public static $table = 'book_test';

    public static $attributes = [
        'book_id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => [
            'max_length' => 64
        ], 'default' => 'the default'],
        'published' => ['type' => 'datetime'],
    ];

    public static $unique = [
        'book_name' => ['name']
    ];

    public static $primary = 'book_id';
}
