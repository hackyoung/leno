<?php
namespace Leno\Type;

use \Leno\Type\TypeStorageInterface; 
use \Leno\Type\Exception\ValueNotUriException;
use \GuzzleHttp\Psr7\Uri;

class UriType extends \Leno\Type implements TypeStorageInterface
{
    protected $regexp = '#^/(?:[^?]*)?(?:\?[^\#]*)?(?:\#[0-9a-z\-\_\/]*)?$#';

    protected function _check($value) : bool
    {
        if($value instanceof Uri) {
            return true;
        }
        if(!preg_match($this->regexp, $value)) {
            throw new ValueNotUriException($this->value_name, $value);
        }
        return true;
    }

    public function toDB($value)
    {
        if($value === null) {
            return null;
        }
        return (string)$value;
    }

    public function toPHP($value)
    {
        if($value === null) {
            return null;
        }
        return new Uri($value);
    }

    public function toType()
    {
        return 'VARCHAR(1024)';
    }
}
