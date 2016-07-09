<?php
namespace Leno\Database\QueryBuilder;

use \Leno\Database\QueryBuilder\Expr\LikeExpr;
use \Leno\Database\QueryBuilder\Expr\NotLikeExpr;
use \Leno\Database\QueryBuilder\Expr\InExpr;
use \Leno\Database\QueryBuilder\Expr\NotInExpr;
use \Leno\Database\QueryBuilder\Expr\MathExpr;

/**
 * 表达式的作用Row不会对表达式作任何处理
 */
abstract class Expr
{
    public static $expr_map = [
        MathExpr::TYPE_EQ => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        MathExpr::TYPE_NEQ => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        MathExpr::TYPE_GT => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        MathExpr::TYPE_LT => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        MathExpr::TYPE_GTE => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        MathExpr::TYPE_LTE => '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr',
        LikeExpr::TYPE => '\\Leno\\Database\\QueryBuilder\\Expr\\LikeExpr',
        NotLikeExpr::TYPE => '\\Leno\\Database\\QueryBuilder\\Expr\\NotLikeExpr',
        InExpr::TYPE => '\\Leno\\Database\\QueryBuilder\\Expr\\InExpr',
        NotInExpr::TYPE => '\\Leno\\Database\\QueryBuilder\\Expr\\NotInExpr',
    ];

    public static function get($expr, $op_1, $op_2)
    {
        $Expr = self::$expr_map[$expr] ?? null;
        if(!$Expr) {
            throw new \Leno\Exception('Expr['.$expr.'] Not Supported');
        }
        if ($Expr == '\\Leno\\Database\\QueryBuilder\\Expr\\MathExpr') {
            return new $Expr($expr, $op_1, $op_2);
        }
        return new $Expr($op_1, $op_2);
    }

    public function __tostring()
    {
        $this->stringlify();
    }

    abstract protected function stringlify();
}
