<?php
namespace Leno;

class Exception extends \Exception
{
    protected $messageTemplate = '%s';

    public function __construct($message = '', $code = null)
    {
        $message = sprintf($this->messageTemplate, $message);
        logger()->error(get_called_class() .': ' . $message);
        parent::__construct($message, $code);
    }
}
