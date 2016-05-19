<?php
namespace Leno\ORM\Adapter\Mysql;

class Executor extends \Leno\ORM\Adapter\Executor
{
    public function keyQuote($key)
    {
        return '`'.$key.'`';
    }

    public function getTableInfo(\Leno\ORM\Table $table)
    {
        $result = $this->query('describe '.$table->getName());
        if($result === false) {
            return false;
        }
        $fields = [];
        do {
            $row = $result->fetch(self::FETCH_ASSOC);
            $attr = [ 'type' => $row['Type'], ];
            if($row['Null'] === 'NO') {
                $attr['null'] = 'NOT NULL';
            }
            if($row['Default']) {
                $attr['default'] = $row['Default'];
            }
            if($row['Key'] === 'Pri') {
                $attr['key'] = $row['primary key'];
            }
            if(!empty($row['Field'])) {
                $fields[$row['Field']] = $attr;
            }
        } while($row);
        return $fields;
    }
}
