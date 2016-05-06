<?php
namespace Test\Model;

class Entity extends \Leno\DataMapper\Mapper
{
    public static $attributes = [
        'user_id' => ['type' => 'uuid',],
        'name' => ['type' => 'string', 'extra' => ['max_length' => 32]],
        'age' => ['type' => 'integer', 'extra' => ['min' => 0, 'max' => 200]],
        'created' => ['type' => 'datetime',],
        'city_id' => ['type' => 'uuid', 'required' => false],
        'home_page' => ['type' => 'uri', 'required' => false],
    ];

    public static $table = 'user';

    public static $primary = 'user_id';

    public static $unique = ['user_id'];

    public static $foreign = [
        'city' => [
            'class' => '\Test\Model\City',
            'local' => 'city_id',
            'foreign' => 'city_id',
        ],
        'books' => [
            'class' => '\Test\Model\Book',
            'local' => 'book_id',
            'foreign' => 'book_id',
            'next' => [
                'class' => '\Test\Model\UserBook',
                'local' => 'user_id',
                'foreign' => 'user_id',
            ]
        ]
    ];
}
