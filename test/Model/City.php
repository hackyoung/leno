<?php
namespace Test\Model;

class City extends \Leno\ORM\Mapper
{
    public static $attributes = [
        'city_id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => [
            'max_length' => 16
        ]]
    ];

    public static $table = 'city';

    public static $unique = ['city_id'];

    public static $primary = 'city_id';
}
