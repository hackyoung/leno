<?php
namespace Leno\Type;

class Exception extends \Leno\Exception
{
    public function __construct($name, $value)
    {
        $message = sprintf('%s[current value: %s]', $name, $value);
        parent::__construct($message);
    }
}
