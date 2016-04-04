<?php
namespace Leno;

trait Singleton
{
    protected static $instance;

    public static function instance()
    {
        if(!self::$instance instanceof self) {
            self::$instance = new self(func_get_args());
        }
        return self::$instance;
    }
}
