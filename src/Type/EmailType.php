<?php
namespace Leno\Type;

use \Leno\Type\StringType;

class EmailType extends StringType
{
    protected $extra = [
        'regexp' => '/^[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}$/',
        'max_length' => 64,
        'min_length' => 0
    ];

    public function setExtra($extra)
    {
        return $this;
    }
}
