<?php
namespace Leno\Doc;

class MethodParser
{
    protected $method;

    protected $comment;

    public function __construct(\ReflectionMethod $method)
    {
        $this->method = $method;
    }

    public function __call($method, $arguments = null)
    {
        return $this->method->__call($method, $arguments);
    }
}
