<?php
namespace Leno;

class Service
{
    protected static $base = 'model.service';

    public static function getService($service_name, $args = [])
    {
        $name = self::$base . '.' . $service_name;
        $class = preg_replace_callback('/^\w|\.\w/', function($matches) {
            return strtoupper(str_replace('.', '\\', $matches[0]));
        }, $name);
        $reflector = new \ReflectionClass($class);
        return $reflector->newInstanceArgs($args);
    }

    public static function setBase($base)
    {
        self::$base = $base;
    }
}
