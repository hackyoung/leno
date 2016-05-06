<?php
namespace Leno\DataMapper\Adapter;

class Mysql extends \Leno\DataMapper\Adapter
{
    protected $label = 'mysql';

    public static function keyQuote($str)
    {
        return '`'.$str.'`';
    }
}
