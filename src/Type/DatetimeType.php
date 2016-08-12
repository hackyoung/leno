<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface;

abstract class DatetimeType extends \Leno\Type implements TypeStorageInterface
{
    protected $format = 'Y-m-d H:i:s';

    protected $regexp = '/^\d{4}(-\d{1,2}){2} \d{1,2}(:\d{1,2}){1,2}$/';

    protected function _check($value) : bool
    {
        if($val instanceof \Datetime) {
            return true;
        }
        if(!preg_match($this->regexp, $val)) {
            throw new \Exception($this->value_name . ' Not A Valid Datetime');
        }
        return true;
    }

    public function toDbType() : string
    {
        return $this->_toType();
    }

    public function toPHP($value)
    {
        if($value === null) {
            return null;
        }
        return new \Datetime($value);
    }

    public function toDB($value)
    {
        if($value === null) {
            return null;
        }
        if($value instanceof \Datetime) {
            return $value->format($this->format);
        }
        return $value;
    }

    abstract protected function _toType();
}
