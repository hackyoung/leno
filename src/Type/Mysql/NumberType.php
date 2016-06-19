<?php
namespace Leno\Type\Mysql;

class NumberType extends \Leno\Type\NumberType
{
    protected function _toType()
    {
        return 'FLOAT';
    }
}
