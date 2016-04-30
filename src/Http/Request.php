<?php
namespace Leno\Http;

class Request extends \GuzzleHttp\Psr7\Request
{
    private $attribute = [];

    public function redirect($url)
    {
    }

    public function withAttribute($attr, $val)
    {
        $this->attribute[$attr] = $val;
    }

    public function getAttribute($attr)
    {
        if(!isset($this->attribute[$attr])) {
             throw new AttributeNotFoundException;
        }
        return $this->attribute[$attr];
    }

    public function hasAttribute($attr) {
        return isset($this->attribute[$attr]);
    }
}
