<?php
namespace Leno\Validator\Type;

class Uri extends \Leno\Validator\Type implements \Leno\DataMapper\TypeStorage
{
    protected $regexp = '#^/(?:[^?]*)?(?:\?[^\#]*)?(?:\#[0-9a-z\-\_\/]*)?$#';

    public function check($value)
    {
        if(!parent::check($value)) {
            return true;
        }
        if($value instanceof \GuzzleHttp\Psr7\Uri) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new \Exception($this->value_name . ' Not A Valid Uri');
        }
        return true;
    }

    public function fromStore($store)
    {
        return new \GuzzleHttp\Psr7\Uri($store);
    }

    public function toStore($value)
    {
        if($value instanceof \GuzzleHttp\Psr7\Uri) {
            return (string)$value;
        }
        return $value;
    }
}
