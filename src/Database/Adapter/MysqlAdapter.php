<?php
namespace Leno\Database\Adapter;

use \Leno\Database\Adapter;

class MysqlAdapter extends Adapter
{

    protected function quote(string $value) : string
    {
        return '`'.$value.'`';
    }

    protected _describeTable(string $table_name)
    {
        $result = $this->execute('DESCRIBE ' . $table_name);
        if($result === false) {
            return false;
        }
        $fields = [];
        do {
            $row = $result->fetch(self::FETCH_ASSOC);
            $attr = [ 'type' => $row['Type'], ];
            if ($row['Null'] === 'NO') {
                $attr['null'] = 'NOT NULL';
            }
            if ($row['Default']) {
                $attr['default'] = $row['Default'];
            }
            if (!empty($row['Field'])) {
                $fields[$row['Field']] = $attr;
            }
        } while($row);

        return $fields;
    }
}
