<?php
namespace Leno\ORM;

use \Leno\Type\Exception\TypeMissingException;

class Type
{
    public static $adapter = 'mysql';

    protected static $type_map = [
        'mysql' => [
        ]
    ];

    public static function getClass($type_label)
    {
        if(!(self::$type_map[self::$adapter][$type_label] ?? false)) {
            throw new TypeMissingException($type_label);
        }
        return self::$type_map[self::$adapter][$type_label];
    }
}
