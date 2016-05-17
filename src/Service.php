<?php
namespace Leno;

abstract class Service
{
    protected static $map = [
        'user' => 'model.service',
        'leno.remote' => 'leno.service.remote.helper',
        'leno.local' => 'leno.service.local'
    ];

    public function __call($method, array $args = null)
    {
        $prefix = substr($method, 0, 3);
        switch($prefix) {
            case 'set':
                $attr = str_replace('set', '', $method);
                $this->attr = $args[0];
                return $this;
        }
        throw new \Leno\Exception (get_called_class() . '::'.$method . ' Not Defined');
    }

    public static function getService($service_name, $args = [])
    {
        foreach(self::$map as $prefix => $base) {
            if(preg_match('/^'.$prefix.'\./', $service_name)) {
                $service_name = preg_replace('/^'.$prefix.'\./', '', $service_name);
                break;
            }
        }
        $name = $base . '.' . $service_name;
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

    public static function register($prefix, $base)
    {
        self::$map[$prefix] = $base;
    }

    abstract public function execute();
}
