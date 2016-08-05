<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface;
use \Leno\Type\Exception\ValueNotInListException;

class EnumType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        $enum_list = $this->extra;
        if(!in_array($value, $enum_list)) {
            throw new ValueNotInListException($this->value_name, $value, $enum_list);
        }
        return true;
    }

    public function toPHP($value)
    {
        return $value;
    }

    public function toDbType() : string
    {
        $enum_list = $this->extra;
        $len = 0;
        foreach($enum_list as $enum) {
            $len = max($len, strlen((string)$enum));
        }
        return 'VARCHAR('.$len.')';
    }

    public function toDB($value) : string
    {
        return (string)$value;
    }
}
