<?php
namespace Leno\Type;

use \Leno\Type\NumberType;

class IntegerNumber extends NumberType
{
    protected $regexp = '/-?\d+/';
}
