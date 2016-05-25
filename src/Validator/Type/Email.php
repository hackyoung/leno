<?php
namespace Leno\Validator\Type;

class Email extends \Leno\Validator\Type\Stringl
{
    protected $regexp = '/^[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}$/';

    public function __construct()
    {
        parent::__construct(null, 0, 256);
    }
}
