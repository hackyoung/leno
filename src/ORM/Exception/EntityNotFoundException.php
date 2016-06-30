<?php
namespace Leno\ORM\Exception;

class EntityNotFoundException extends \Leno\Exception
{
    protected $messageTemplate = 'Cant Find Entity: (%s) By Id: (%s)';
}
