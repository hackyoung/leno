<?php
namespace Leno\Core\Type;

class Ipv4 extends \Leno\Core\Type
{
    protected $regexp = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';

    public function check($value)
    {
        if(!parent::check($value)) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new \Exception($this->value_name . ' Not A Valid Ipv4 Address');
        }
        return true;
    }
}
