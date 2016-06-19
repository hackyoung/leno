<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface; 
use \Leno\Type\Exception\ValueNotUuidException;

abstract class UuidType extends \Leno\Type implements TypeStorageInterface
{
    protected $regexp = '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i';

    protected function _check($value) : bool
    {
        if(!preg_match($this->regexp, $value)) {
            throw new ValueNotUuidException($this->value_name, $value);
        }
        return true;
    }

    public function toDB($value) : string
    {
        if($value === null) {
            return null;
        }
        return (string)$value;
    }

    public function toPHP($value)
    {
        return $value;
    }

    public function toDbType() : string
    {
        return $this->_toType();
    }

    abstract protected function _toType();
}
