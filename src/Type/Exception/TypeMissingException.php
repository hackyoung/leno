<?php
namespace Leno\Type\Exception;

class TypeMissingException extends \Leno\Type\Exception
{
    protected $messageTemplate = 'Type: %s Missing';
}
