<?php
namespace Leno\Type\Mysql;

class ArrayType extends \Leno\Type\ArrayType
{
    protected function _toType()
    {
        return 'TEXT';
    }

    protected function _toPHP($value)
    {
        return explode(',', $value);
    }

    protected function _toDB($value)
    {
        return implode(',', $value);
    }

    public function in($value, $arr)
    {
        $tmp = 'FIND_IN_SET(\'%s\', %s)';
        if (is_array($arr)) {
            $arr = '\'' . implode('\',\'', $arr).'\'';
        }
        return sprintf($tmp, $value, $arr);
    }
}
