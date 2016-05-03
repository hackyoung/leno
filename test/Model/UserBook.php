<?php
namespace Test\Model;

class UserBook extends \Leno\DataMapper\Mapper
{
	public static $attributes = [
		'book_id' => ['type' => 'uuid'],
		'user_id' => ['type' => 'uuid'],
	];

	public static $unique = ['book_id', 'user_id'];

	public static $table = 'user_book';
}
