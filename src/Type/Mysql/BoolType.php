<?php
namespace Leno\Type\Mysql;

class BoolType extends \Leno\Type\BoolType
{
    protected function _toDbType() : string
    {
        return 'TINYINT(1)';
    }

    protected function _toDB($value) 
    {
        return ($value === true) ? 1 : 0;
    }

    protected function _toPHP($value)
    {
        return ((int)$value === 1) ? true : false;
    }
}
