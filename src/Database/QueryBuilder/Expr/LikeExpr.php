<?php
namespace Leno\Database\QueryBuilder\Expr;

use \Leno\Database\QueryBuilder\Expr;

class LikeExpr extends Expr
{
    protected $op_1;

    protected $op_2;

    public function __construct($op_1, $op_2)
    {
        $this->op_1 = $op_1;
        $this->op_2 = $op_2;
    }

    protected function stringlify()
    {
        return sprintf('%s LIKE %s', $this->op_1, $this->op_2);
    }
}
