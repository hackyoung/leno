<?php
namespace Model;

abstract class Service extends \Leno\Service
{
    abstract public function execute(callable $callable = null);
}
