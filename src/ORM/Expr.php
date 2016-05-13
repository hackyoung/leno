<?php
namespace Leno\ORM;

class Expr
{
    private $value;

    public function __construct($val)
    {
        $this->value = $val;
    }

    public function __tostring()
    {
        return $this->value . '';
    }
}
