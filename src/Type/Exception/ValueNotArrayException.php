<?php
namespace Leno\Type\Exception;

class ValueNotArrayException extends \Leno\Type\Exception
{
    protected $messageTemplate = '%s Not Array';

    public function __construct($name, $value)
    {
        $message = sprintf('%s[current value: %s]', $name, $value);
        parent::__construct($message);
    }
}
