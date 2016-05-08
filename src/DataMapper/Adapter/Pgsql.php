<?php
namespace \Leno\DataMapper\Adapter;

class Pgsql extends \Leno\DataMapper\Adapter
{
    protected $label = 'pgsql';

    public static function keyQuote($str)
    {
        return '"'.$str.'"';
    }

    public function in($val, $set)
    {
        $val = $this->normalizeSet($val);
        $set = $this->normalizeSet($set);
        return sprintf('(%s && %s)', $val, $set);
    }

    private function normalizeSet($val)
    {
        if(is_array($val)) {
            $val = implode(', ', $val);
        }
        if(!$val instanceof \Leno\DataMapper\Expr) {
            $val = '{'.$val.'}';   
        }
        return $val;
    }
}
