<?php
namespace Test\Model;
/**
 * 这是一个Book实体
 * @name Book
 */
class Book extends \Leno\DataMapper\Mapper
{
    public static $attributes = [
        'book_id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => ['max_length'=> 256]],
    ];

    public static $primary = 'book_id';

    public static $unique = ['book_id'];

    public static $table = 'book';
}
