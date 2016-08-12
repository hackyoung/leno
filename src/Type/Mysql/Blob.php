<?php
namespace Leno\Type\Mysql;

use \Leno\Type\TypeStorageInterface;

class BlobType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        return $value === $value;
    }

    protected function toDbType() : string
    {
        return 'BLOB';
    }

    protected function toPHP($value)
    {
        return $value;
    }

    protected function toDB($value)
    {
        return $value;
    }
}
