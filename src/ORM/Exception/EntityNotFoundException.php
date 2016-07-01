<?php
namespace Leno\ORM\Exception;

class EntityNotFoundException extends \Leno\Exception
{
    protected $messageTemplate = 'Entity: %s Not Found';

    public function __construct($entity, $id)
    {
        parent::__construct($entity.'['.$id.']');
    }
}
