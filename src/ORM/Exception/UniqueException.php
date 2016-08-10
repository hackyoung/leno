<?php
namespace Leno\ORM\Exception;

class UniqueException extends \Leno\Exception
{
    protected $messageTemplate = 'Entity: %s existed';

    private $field;

    public function __construct($entity, $field)
    {
        $this->field = $field;
        if (is_array($field)) {
            $field = implode(',', $field);
        }
        parent::__construct($entity.'['.$field.']');
    }

    public function getField()
    {
        return $this->field;
    }
}
