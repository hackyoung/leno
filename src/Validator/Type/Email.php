<?php
namespace Leno\Validator\Type;

class Email extends \Leno\Validator\Type
{
    protected $regexp = '/^[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}$/';

    public function check($value)
    {
        if(!parent::check($value)) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new \Exception($this->value_name . ' Not A Valid Email Address');
        }
        return true;
    }
}
