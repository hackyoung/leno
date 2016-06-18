<?php
namespace Leno;

use \Leno\Service\Exception as ServiceException;

class Service
{
    use \Leno\Traits\Magic;

    protected static $map = [
        'user' => 'model.service',
        'leno.remote' => 'leno.service.remote.helper',
        'leno.local' => 'leno.service.local'
    ];

    public function __call($method, array $args = null)
    {
        return $this->__magic_call($method, $args);
    }

    public function validate($val, $rules)
    {
        $type = Type::get($rules['type'])->setExtra($rules['extra'] ?? []);
        if(isset($rules['required'])) {
            $type->setRequired($rules['required']);
        }
        if(isset($rules['allow_empty'])) {
            $type->setAllowEmpty($rules['allow_empty']);
        }
        return $type->check($val);
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
            throw new ServiceException('service \''.$service_name.'\' Not Found');
        }
        $service = (new \ReflectionClass($class))->newInstanceArgs($args);
        if(!$service instanceof self) {
            throw new ServiceException('\''.$service_name.'\' Not A Service');
        }
        return $service;
    }

    public static function register($prefix, $base)
    {
        self::$map[$prefix] = $base;
    }
}
