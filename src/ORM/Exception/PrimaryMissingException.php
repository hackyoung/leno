<?php
namespace Leno\ORM\Exception;

class PrimaryMissingException extends \Leno\Exception
{
    protected $messageTemplate = 'Entity: %s Missing Primary Defination';
}
