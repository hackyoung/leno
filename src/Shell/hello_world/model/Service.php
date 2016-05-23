<?php
namespace Model;

/**
 * 应用程序的service都应该继承自这个类
 */
abstract class Service extends \Leno\Service
{
    abstract public function execute(callable $callable = null);
}
