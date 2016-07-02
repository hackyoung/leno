<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class User extends Entity
{
    public static $table = 'user_test';

    public static $attributes = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'sensitive' => true, 'null' => false, 'extra' => [
            'max_length' => 64
        ], 'default' => 'the default'],
        'age' => ['type' => 'integer', 'null' => false, 'default' => 20]
    ];

    public static $primary = 'id';
}
