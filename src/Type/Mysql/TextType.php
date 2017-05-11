<?php
namespace Leno\Type\Mysql;

use \Leno\Type\TypeStorageInterface;

class TextType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        return $value == $value;
    }

    public function toDbType() : string
    {
        return 'TEXT';
    }

    public function toPHP($value)
    {
        return $value;
    }

    public function toDB($value)
    {
        return $value;
    }
}
