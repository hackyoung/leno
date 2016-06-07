<?php
namespace Leno;

class Exception extends \Exception
{
    protected $messageTemplate = '%s';

    public function __construct($message = null, $code = null)
    {
        if($message !== null) {
            $this->message = $message;
        }
        if($code !== null) {
            $this->code = $code;
        }
        parent::__construct(sprintf($this->messageTemplate, $this->message), $this->code);
    }
}
