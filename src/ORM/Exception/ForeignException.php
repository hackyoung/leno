<?php
namespace Leno\ORM\Exception;

class ForeignException extends \Leno\Exception
{
    protected $messageTemplate = 'Entity: %s constraint failed';

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
