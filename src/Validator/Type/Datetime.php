<?php
namespace Leno\Validator\Type;

class Datetime extends \Leno\Validator\Type implements \Leno\DataMapper\TypeStorage
{
    protected $format = 'Y-m-d H:i:s';

    protected $regexp = '/^\d{4}(-\d{1,2}){2} \d{1,2}(:\d{1,2}){1,2}$/';

    public function check($val) {
        if(!parent::check($val) ) {
            return true;
        }
        if($val instanceof \Datetime) {
            return true;
        }
        if(!preg_match($this->regexp, $val)) {
            throw new \Exception($this->value_name . ' Not A Valid Datetime');
        }
        return true;
    }

    public function toStore($value)
    {
        if($value instanceof \Datetime) {
            $value = $value->format($this->format);
        }
        return $value;
    }

    public function fromStore($value)
    {
        if(!$value instanceof \Datetime) {
            $value = new \Datetime($value);
        }
        return $value;
    }
}
