<?php
namespace Leno\Database;

/**
 * 表达式的作用Row不会对表达式作任何处理
 */
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
