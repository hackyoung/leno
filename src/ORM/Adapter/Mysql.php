<?php
namespace Leno\ORM\Adapter;

class Mysql extends \Leno\ORM\Adapter
{
    protected $label = 'mysql';

    public static function keyQuote($str)
    {
        return '`'.$str.'`';
    }

    public function getFieldsInfo($table)
    {
        $result = $this->exec('DESCRIBE '.$table);
        $fields = [];
        do {
            $row = $result->fetch(self::FETCH_ASSOC);
            $fields[] = $row;
        }while($row);
        return $fields;
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
        if(!$val instanceof \Leno\ORM\Expr) {
            $val = '\''.$val.'\'';   
        }
        return $val;
    }
}
