<?php
namespace Leno\Database\QueryBuilder\Expr;

use \Leno\Database\QueryBuilder\Expr;

/**
 * 表达式的作用Row不会对表达式作任何处理
 */
class NormalExpr extends Expr
{
    private $value;

    public function __construct($val)
    {
        $this->value = $val;
    }

    protected function stringlify()
    {
        return $this->value . '';
    }
}
