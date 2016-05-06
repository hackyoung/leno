<?php
namespace Leno;

abstract class Service
{
    protected static $base = 'model.service';

    public static function getService($service_name, $args = [])
    {
        $name = self::$base . '.' . $service_name;
        $class = preg_replace_callback('/^\w|\.\w/', function($matches) {
            return strtoupper(str_replace('.', '\\', $matches[0]));
        }, $name);
        if(!class_exists($class)) {
            throw new \Leno\Service\Exception('service \''.$service_name.'\' Not Found');
        }
        $service = (new \ReflectionClass($class))->newInstanceArgs($args);
        if(!$service instanceof self) {
            throw new \Leno\Service\Exception('\''.$service_name.'\' Not A Service');
        }
        return $service;
    }

    public static function setBase($base)
    {
        self::$base = $base;
    }

    abstract function execute();
}
