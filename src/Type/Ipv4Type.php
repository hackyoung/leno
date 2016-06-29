<?php
namespace Leno\Type;

use \Leno\Type\StringType;

class Ipv4Type extends StringType
{
    protected $extra = [
        'regexp' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',
        'min_length' => 0,
        'max_length' => 16
    ];

    public function setExtra($extra)
    {
        return $this;
    }
}
