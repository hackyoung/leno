<?php
namespace Leno\Console\Formatter;

abstract class Helper
{
    public static $map = [
        'info' => '\\Leno\\Console\\Formatter\\Helper\\Info',
        'keyword' => '\\Leno\\Console\\Formatter\\Helper\\Keyword',
    ];
    abstract function format($text);

    public static function getHelper($name)
    {
        $class = self::$map[$name];
        return new $class;
    }
}
