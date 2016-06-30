<?php
namespace Leno\Type;

use \Leno\Type\NumberType;

class IntegerType extends NumberType
{
    protected $regexp = '/-?\d+/';
}
