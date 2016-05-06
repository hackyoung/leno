<?php
namespace \Leno\DataMapper\Adapter;

class Pgsql extends \Leno\DataMapper\Adapter
{
    protected $label = 'pgsql';

    public static function keyQuote($str)
    {
        return '"'.$str.'"';
    }
}
