<?php
namespace Leno\Configure;

class PhpConfigure extends \Leno\Configure
{
    protected function parse($file) : array
    {
        $pathfile = $this->base_dir .'/'. $file;

        if (is_file($pathfile)) {
            return include $pathfile;
        }

        return [];
    }

    protected function store() : string
    {
    }
}
