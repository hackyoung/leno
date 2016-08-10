<?php
namespace Test\Model;

class Author extends \Leno\ORM\Entity
{
    public static $table = 'author';

    public static $primary = 'id';

    public static $attributes = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => [
            'max_length' => 32
        ]],
        'book_ids' => ['type' => 'array', 'is_nullable' => true],
        'created' => ['type' => 'datetime']
    ];

    public static $foreign = [
        'book' => [
            'entity' => '\\Test\\Model\\Book',
            'local_key' => 'book_ids',
            'foreign_key' => 'book_id',
            'is_array' => true
        ]
    ];

    public static $unique = [
        'name' => ['name']
    ];
}
