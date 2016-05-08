<?php
namespace Leno\DataMapper\Adapter;

class Mysql extends \Leno\DataMapper\Adapter
{
    protected $label = 'mysql';

    public static function keyQuote($str)
    {
        return '`'.$str.'`';
    }

    public function in($val, $set)
    {
        $val = $this->normalizeSet($val);
        $set = $this->normalizeSet($set);
        return sprintf('FIND_IN_SET(%s, %s)', $val, $set);
    }

    private function normalizeSet($val)
    {
        if(is_array($val)) {
            $val = implode(', ', $val);
        }
        if(!$val instanceof \Leno\DataMapper\Expr) {
            $val = '\''.$val.'\'';   
        }
        return $val;
    }
}
