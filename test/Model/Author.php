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
        'created' => ['type' => 'datetime']
    ];

    public static $foreign_by = [
        'book' => [
            'entity' => '\\Test\\Model\\Book',
            'attr' => 'author'
        ]
    ];

    public static $unique = [
        'name' => ['name']
    ];
}
