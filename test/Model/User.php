<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class User extends Entity
{
    public static $table = 'user_test';

    public static $attributes = [
        'id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => [
            'max_length' => 64
        ], 'default' => 'the default'],
        'age' => ['type' => 'integer', 'default' => 20]
    ];

    public static $primary = 'id';

    public static $unique = [
        'user_name' => ['name']
    ];

    public static $foreign = [
        'book' => [
            'entity' => '\\Test\\Model\\Book',
            'local_key' => 'id',
            'foreign_key' => 'id',
            'bridge' => [
                'entity' => '\\Test\\Model\\UserBook',
                'local' => 'author_id',
                'foreign' => 'book_id'
            ]
        ]
    ];
}
