<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface;
use \Leno\Type\Exception\ValueNotStringException;
use \Leno\Type\Exception\ValueLengthException;
use \Leno\Type\Exception\ValueNotMatchedRegexpException;

class StringType extends \Leno\Type implements TypeStorageInterface
{
    protected function _check($value) : bool
    {
        if(!is_string($value)) {
            throw new ValueNotStringException($this->value_name, $value);
        }
        $regexp = $this->extra['regexp'] ?? null;
        if($regexp && !preg_match($regexp, $value)) {
            throw new ValueNotMatchedRegexpException($this->value_name, $value, $regexp);
        }
        $len = strlen($val);
        $max_length = $this->extra['max_length'];
        if($max_length && $len > $max_length) {
            throw new ValueLengthException($this->value_name, $value);
        }
        $min_length = $this->extra['min_length'] ?? null;
        if($min_length && $len < $min_length) {
            throw new ValueLengthException($this->value_name, $value);
        }
        return true;
    }

    public function toType() : string
    {
        $max_length = $this->extra['max_length'];
        return 'VARCHAR('.$max_length.')';
    }

    public function toPHP($value)
    {
        return $value;
    }

    public function toDB($value) : string
    {
        return $value;
    }
}
