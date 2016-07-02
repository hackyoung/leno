<?php
namespace Leno\Database\Adapter;

use \Leno\Database\Adapter;

class MysqlAdapter extends Adapter
{
    protected function quote(string $value) : string
    {
        return '`'.$value.'`';
    }

    protected function _describeTable(string $table_name)
    {
        try {
            $result = $this->execute('DESCRIBE ' . $table_name);
        } catch (\Exception $e) {
            return false;
        }
        $fields = [];
        do {
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            $attr = [
                'type' => strtoupper($row['Type']),
                'null' => 'NULL'
            ];
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

    protected function _describeConstraint(string $table_name)
    {
    }
}
