<?php
namespace Leno\Type\Mysql;

class ArrayType extends \Leno\Type\ArrayType
{
    protected function _toType()
    {
        return 'JSONB';
    }

    protected function _toPHP($value)
    {
        return json_decode($value, true);
    }

    protected function _toDB($value)
    {
    }
}
