<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface; 
use \Leno\Type\Exception\ValueNotUrlException;

class UrlType extends \Leno\Type implements TypeStorageInterface
{
    protected $regexp = '#^[a-z]+://[0-9a-z\-\.]+\.[0-9a-z]{1,4}(?:\d+)?(?:/[^\?]*)?(?:\?[^\#]*)?(?:\#[0-9a-z\-\_\/]*)?$#';

    protected function _check($value) : bool
    {
        if(!preg_match($this->regexp, $value)) {
            throw new ValueNotUrlException($this->value_name, $value);
        }
        return true;
    }

    public function toDB($value)
    {
        return (string)$value;
    }

    public function toPHP($value)
    {
        return new Uri($value);
    }

    public function toType()
    {
        return 'VARCHAR(1024)';
    }
}
