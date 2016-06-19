<?php
namespace Leno\Type;

use \Leno\Type\NumberType;

abstract class IntegerType extends NumberType
{
    protected $regexp = '/-?\d+/';
}
