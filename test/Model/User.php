<?php
namespace Test\Model;

use \Leno\ORM\Entity;

class User extends Entity
{
    public static $table = 'user_test';

    public static $attributes = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'null' => false, 'extra' => ['max_length' => 64]],
        'age' => ['type' => 'integer', 'null' => false]
    ];

    public static $primary = 'id';
}
