<?php
namespace Leno\Type\Exception;

class ValueRequiredException extends \Leno\Type\Exception
{
    protected $messageTemplate = '%s Required';

    public function __construct($name)
    {
        parent::__construct($name, 'null');
    }
}
