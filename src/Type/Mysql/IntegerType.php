<?php
namespace Leno\Type;

class IntegerType extends \Leno\Type\IntegerType
{
    protected function _toType()
    {
        return 'INT(11)';
    }
}
