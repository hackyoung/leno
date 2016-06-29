<?php
namespace Leno\Type;

use \Leno\Type\StringType;

class PhoneType extends StringType
{
    protected $extra = [
        'regexp' => '/^1[3458]\d{9}$/',
        'min_length' => 0,
        'max_length' => 11
    ];

    public function setExtra($extra)
    {
        return $this;
    }
}
