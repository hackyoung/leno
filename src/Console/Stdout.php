<?php
namespace Leno\Console;

class Stdout
{
    const FORMAT_ERROR = "\x1b[0;31mleno.ERROR:\t%s\x1b[0m\n";

    const FORMAT_WARN = "\x1b[0;33mleno.WARNING:\t%s\x1b[0m\n";

    const FORMAT_NOTICE = "\x1b[0;34mleno.NOTICE:\t%s\x1b[0m\n";

    const FORMAT_DEBUG = "\x1b[0;35mleno.DEBUG:\t%s\x1b[0m\n";

    /**
     * @description 向终端输出警告
     */
    public static function warn($message)
    {
        echo sprintf(self::FORMAT_WARN, $message);
    }

    /**
     * @description 向终端输出提示
     */
    public static function notice($message)
    {
        echo sprintf(self::FORMAT_NOTICE, $message);
    }

    /**
     * @description 向终端输出错误
     */
    public static function error($message)
    {
        echo sprintf(self::FORMAT_ERROR, $message);
    }

    /**
     * @description 向终端输出debug信息
     */
    public static function debug($message)
    {
        echo sprintf(self::FORMAT_DEBUG, $message);
    }

    /**
     * @description 向终端输出信息
     */
    public static function output($message)
    {
        echo $message . "\n";
    }
}
