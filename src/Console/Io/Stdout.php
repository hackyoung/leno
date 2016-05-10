<?php
namespace Leno\Console\Io;

class Stdout
{
    /**
     * @description 向终端输出警告
     */
    public static function warn($message)
    {
        (new \Leno\Console\Formatter)->setInput(
            "<warn>leno.WARNING\t".$message.'</warn>'
        )->format();
        echo "\n";
    }

    /**
     * @description 向终端输出提示
     */
    public static function notice($message)
    {
        (new \Leno\Console\Formatter)->setInput(
            "<notice>leno.NOTICE\t".$message.'</notice>'
        )->format();
        echo "\n";
    }

    /**
     * @description 向终端输出错误
     */
    public static function error($message)
    {
        (new \Leno\Console\Formatter)->setInput(
            "<error>leno.ERROR\t".$message.'</error>'
        )->format();
        echo "\n";
    }

    /**
     * @description 向终端输出debug信息
     */
    public static function debug($message)
    {
        (new \Leno\Console\Formatter)->setInput(
            "<debug>leno.DEBUG\t".$message.'</debug>'
        )->format();
        echo "\n";
    }

    /**
     * @description 向终端输出debug信息
     */
    public static function info($message)
    {
        (new \Leno\Console\Formatter)->setInput(
            "<info>leno.INFO\t".$message.'</info>'
        )->format();
        echo "\n";
    }

    public static function output($message)
    {
        (new \Leno\Console\Formatter)->setInput($message)->format();
        echo "\n";
    }
}
