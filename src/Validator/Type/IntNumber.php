<?php
namespace Leno\Validator\Type;

class IntNumber extends \Leno\Validator\Type\Number
{
    protected $regexp = '/-?\d+/';
}
