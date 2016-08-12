<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface;

abstract class BoolType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        if($value === true || $value === false) {
            return true;
        }
        return false;
    }

    public function toDbType() : string
    {
        return $this->_toDbType();
    }

    public function toDB($value)
    {
        return $this->_toDB($value);
    }

    public function toPHP($value)
    {
        return $this->_toPHP($value);
    }

    abstract protected function _toDbType();

    abstract protected function _toDB($value);

    abstract protected function _toPHP($value);
}
