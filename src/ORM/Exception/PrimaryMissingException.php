<?php
namespace Leno\ORM\Exception;

class PrimaryMissingException extends \Leno\Exception
{
    protected $messageTemplate = '%s Need A Primary Defination';
}
