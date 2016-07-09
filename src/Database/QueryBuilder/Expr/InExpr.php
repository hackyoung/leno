<?php
namespace Leno\Database\QueryBuilder\Expr;

use \Leno\Database\QueryBuilder\Expr;

class InExpr extends Expr
{
    protected $op_1;

    protected $op_2;

    protected $template = '%s IN (%s)';

    public function __construct($op_1, $op_2)
    {
        $this->op_1 = $op_1;
        $this->op_2 = $op_2;
    }

    public function setTempalte($template)
    {
        $this->template = $template;
        return $this;
    }

    protected function stringlify()
    {
        return sprintf($this->template, $this->op_1, $this->op_2);
    }
}
