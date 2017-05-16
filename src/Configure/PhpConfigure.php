<?php
namespace Leno\Configure;

class PhpConfigure extends \Leno\Configure
{
    protected function parse($file) : array
    {
        if (is_file($file)) {
            return include $file;
        }

        return [];
    }

    protected function store() : string
    {
    }
}
