<?php
namespace Leno;

/**
 * 自动加载类
 */
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
        $base_dir = '/' . preg_replace('/^\//', '', $base_dir);
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
        $class = str_replace('\\', '/', preg_replace('/\\$/', '', $class));
        foreach(self::$map as $name_prefix => $path_prefix) {
            if(preg_match('/^'.str_replace('/', '\/', $name_prefix).'/', $class)) {
                $right = $path_prefix . preg_replace('/^'.str_replace('/', '\/', $name_prefix).'/', '', $class);
                $class_file = ROOT . $right . self::SUFFIX;
                if(file_exists($class_file)) {
                    require_once $class_file;
                }
            }
        }
        if(file_exists(ROOT . '/' . $class . self::SUFFIX)) {
            require_once ROOT . '/' . $class . self::SUFFIX;
        }
    }

    public function execute()
    {
        spl_autoload_register([$this, 'load']);
    }
}
