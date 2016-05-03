<?php
namespace Leno\Validator\Type;

class Phone extends \Leno\Validator\String1
{
    protected $regexp = '/^[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}$/';

    public function __construct()
    {
        parent::__construct(null, 0, 11);
    }

    public function check($value)
    {
        return parent::check($value);
    }
}
