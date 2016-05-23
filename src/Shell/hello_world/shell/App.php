<?php
namespace Shell;

/**
 * 所有命令行工具的父类
 */
abstract class App extends \Leno\Shell
{
    abstract public function main();
}
