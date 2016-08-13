<?php
namespace Leno\Type;

class Exception extends \Leno\Exception
{
    public function __construct($name, $value)
    {
        ob_start();
        var_dump($value);
        $v_s = ob_get_contents();
        ob_end_clean();
        $message = sprintf('%s[current value: %s]', $name, $v_s);
        parent::__construct($message);
    }
}
