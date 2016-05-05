<?php
namespace Leno;

class AutoLoader
{
    const SUFFIX = '.php';

    public static $map = [];

    protected static $instance;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public static function register($namespace, $base_dir)
    {
        self::$map[$namespace] = $base_dir;
    }

    public static function instance()
    {
        if(!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function load($class)
    {
        $class = preg_replace('/\\$/', '', $class);
        foreach(self::$map as $name_prefix => $path_prefix) {
            if(preg_match('/^'.$name_prefix.'/', $class)) {
                $class = preg_replace('/'.$name_prefix.'/', '', $class);
                break;
            }
            $path_prefix = '/';
        }
        $classFile = ROOT . $path_prefix . strtr($class, '\\', '/') . self::SUFFIX;
        if(file_exists($classFile)) {
            require_once $classFile;
        }
    }

    public function execute()
    {
        spl_autoload_register([$this, 'load']);
    }
}
