<?php
namespace Leno\Validator\Type;

class Url extends \Leno\Validator\Type
{
    protected $regexp = '#^[a-z]+://[0-9a-z\-\.]+\.[0-9a-z]{1,4}(?:\d+)?(?:/[^\?]*)?(?:\?[^\#]*)?(?:\#[0-9a-z\-\_\/]*)?$#';

    public function check($value)
    {
        if(!parent::check($value)) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new \Exception($this->value_name . ' Not A Valid Url');
        }
        return true;
    }
}
