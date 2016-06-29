<?php
namespace Leno\Type;

interface TypeCheckInterface
{
    public function check($value) : bool;
}
