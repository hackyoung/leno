<?php
namespace Leno\Type\Exception;

class ValueNotAllowEmptyException extends \Leno\Type\Exception
{
    protected $messageTemplate = '%s Not Allow Empty';

    public function __construct($name, $val)
    {
        $message = sprintf('%s[current value: %s]', $name, $val);
        parent::__construct($message);
    }
}
