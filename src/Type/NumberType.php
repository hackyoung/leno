<?php
namespace Leno\Type;

use \Leno\Type\Exception\ValueNumberException;
use \Leno\Type\Exception\ValueNotNumberException;

class NumberType extends \Leno\Type implements TypeStorageInterface
{
    protected $regexp = '/-?\d+(\.\d+)?/';

    protected function _check($value) : bool
    {
        if(!preg_match($this->regexp, (string)$value)) {
            throw new ValueNotNumberException($this->value_name, $value);
        }
        $min = $this->extra['min'] ?? null;
        $max = $this->extra['max'] ?? null;
        if($min && $value < $min || $max && $value > $max) {
            throw new ValueNumberException($this->value_name, $value);
        }
        return true;
    }

    public function toPHP($value)
    {
        return $value;
    }

    public function toDB($value) : string
    {
        return (string)$value;
    }

    public function toType()
    {
        return 'INT';
    }
}
