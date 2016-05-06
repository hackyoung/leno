<?php
namespace Leno;

class Exception extends \Exception
{
    protected $messageTemplate = 'leno: %s';

    public function __construct($message = '', $code = null)
    {
        parent::__construct(sprintf($this->messageTemplate, $message), $code);
    }
}
