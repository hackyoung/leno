<?php
namespace Leno\Database\SqlBuilder;

use \Leno\Database\QueryBuilder\Expr;

class Condition
{
    const EXP_AND = 'AND';

    const EXP_OR = 'OR';

    const EXP_QUOTE_BEGIN = '(';

    const EXP_QUOTE_END = ')';

    private $condition = [];

    public function addExpr(Expr $expr)
    {
        if ($this->condition[-1] instanceof Expr) {
            $this->and();
        }
        $this->condition[] = $expr;
        return $this;
    }

    public function quoteBegin()
    {
        if ($this->condition[-1] instanceof Expr) {
            $this->and();
        }
        $this->condition[] = self::EXP_QUOTE_BEGIN;
        return $this;
    }

    public function quoteEnd()
    {
        $this->condition[] = self::EXP_QUOTE_END;
        return $this;
    }

    public function and()
    {
        $this->condition[] = self::EXP_AND;
        return $this;
    }

    public function or()
    {
        $this->condition[] = self::EXP_OR;
        return $this;
    }

    public function merge(Condition $condi, $op = self::EXP_AND)
    {
        $this->condition[] = $op;
        $this->condition = $this->condition + $condi->toArray();
    }

    public function toArray()
    {
        return $this->condition;
    }

    public function empty()
    {
        return empty($this->condition);
    }

    public function __tostring()
    {
        return implode(' ', $this->condition);
    }
}
