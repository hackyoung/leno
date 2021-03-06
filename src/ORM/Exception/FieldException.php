<?php
namespace Leno\ORM\Exception;

class FieldException extends \Leno\Exception
{
    protected $messageTemplate = '%s Not Good For Save';

    private $field;

    public function __construct($field)
    {
        $this->field = $field;
        parent::__construct($field);
    }

    public function getField()
    {
        return $this->field;
    }
}
