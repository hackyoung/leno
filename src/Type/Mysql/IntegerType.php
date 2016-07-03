<?php
namespace Leno\Type\Mysql;

class IntegerType extends \Leno\Type\IntegerType
{
    protected function _toType()
    {
        return 'INT(11)';
    }
}
