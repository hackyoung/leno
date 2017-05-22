<?php
namespace Leno\Configure;

class JsonConfigure extends \Leno\Congifure
{
    protected function parse($file) : array
    {
        $content = file_get_contents($file);
        return json_decode($content, true);
    }

    protected function store() : string
    {
    }
}
