<?php
namespace Leno\Type\Mysql;

use \Leno\Type\TypeStorageInterface;

class BlobType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        return $value === $value;
    }

    public function toDbType() : string
    {
        return 'BLOB';
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
