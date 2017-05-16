<?php
namespace Leno\Configure;

class PhpConfigure extends \Leno\Configure
{
    protected function parse($file) : array
    {
        return include $file;
    }

    protected function store() : string
    {
    }
}
