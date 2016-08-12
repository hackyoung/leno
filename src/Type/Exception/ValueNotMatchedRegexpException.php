<?php
namespace Leno\Type\Exception;

class ValueNotMatchedRegexpException extends \Leno\Type\Exception
{
    protected $messageTemplate = '%s Not A Valid String';

    public function __construct($name, $value, $regexp)
    {
        parent::__construct($name, $value . ': regexp:' .$regexp);
    }
}
