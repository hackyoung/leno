<?php
namespace Leno\Core\Type;

class IntNumber extends \Leno\Core\Type\Number
{
    protected $regexp = '/-?\d+/';
}
