<?php
namespace Leno\Validator\Type;

class Phone extends \Leno\Validator\Type\Stringl
{
    protected $regexp = '/^1[3458]\d{9}$/';

    public function __construct()
    {
        parent::__construct(null, 0, 11);
    }
}
