<?php
namespace Leno\Type\Mysql;

class DatetimeType extends \Leno\Type\DatetimeType
{
    protected function _toType()
    {
        return 'DATETIME';
    }
}
