<?php
namespace Leno\Type;

use \Leno\Type\ArrayType;
use \Leno\Type\TypeStorageInterface;

class JsonType extends ArrayType implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        if(is_string($value)) {
            $value = json_decode($value, true);
        }
        return parent::_check($value);
    }

    public function toDB($value)
    {
        if(!is_string($value)) {
            return json_encode($value);
        }
        return $value;
    }

    public function toPHP($value)
    {
        if(is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    public function toDbType() : string
    {
        return 'JSONB';
    }
}
