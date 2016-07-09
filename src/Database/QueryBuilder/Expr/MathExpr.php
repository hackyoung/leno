<?php
namespace Leno\Database\QueryBuilder\Expr;

use \Leno\Database\QueryBuilder\Expr;

class MathExpr extends Expr
{
    const TYPE_EQ = 'eq';

    const TYPE_NEQ = 'neq';

    const TYPE_GT = 'gt';

    const TYPE_LT = 'lt';

    const TYPE_GTE = 'gte';

    const TYPE_LTE = 'lte';

    public static $type_map = [
        self::TYPE_EQ => '=',
        self::TYPE_NOT_EQ => '!=',
        self::TYPE_GT => '>',
        self::TYPE_LT => '<',
        self::TYPE_GTE => '>=',
        self::TYPE_LTE => '<='
    ];

    protected $type;

    protected $op_1;

    protected $op_2;

    public function __construct($type, $op_1, $op_2)
    {
        $this->type = $type;
        $this->op_1 = $op_1;
        $this->op_2 = $op_2;
    }

    protected function stringlify()
    {
        $operator = self::$type_map[$this->type];
        return sprintf('%s %s %s', $this->op_1, $operator, $this->op_2);
    }
}
