<?php
namespace Leno;

class Exception extends \Exception
{
    protected $messageTemplate = '%s';

    public function __construct($message = '', $code = null)
    {
        $message = sprintf($this->messageTemplate, $message);
        parent::__construct($message, $code);
    }
}
