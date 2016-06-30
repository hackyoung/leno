<?php
namespace Leno\Type\Mysql;

class UuidType extends \Leno\Type\UuidType
{
    protected function _toType()
    {
        return 'VARCHAR(36)';
    }
}
