<?php
namespace Leno\Database\Adapter;

use \Leno\Database\Adapter;

class MysqlAdapter extends Adapter
{
    protected function quote(string $value) : string
    {
        return '`'.$value.'`';
    }

    protected function _describeColumns(string $table_name)
    {
        $sql = 'SELECT '.
            'DATA_TYPE as type,'.
            'COLUMN_NAME as field,'.
            'COLUMN_DEFAULT as default,'.
            'IS_NULLABLE as is_nullable '.
            'FROM COLUMNS WHERE TABLE_NAME = \''.$table_name.'\'';
        $result = $this->execute($sql);
        $fields = [];
        foreach ($result as $row) {
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            $attr = [
                'type' => strtoupper($row['type']),
                'is_nullable' => true
            ];
            if ($row['is_nullable'] === 'NO') {
                $attr['is_nullable'] = false;
            }
            $attr['default'] = $row['default'] == 'NULL' ? null : $row['default'];
            $fields[$row['field']] = $attr;
        }
        return $fields;
    }

    protected function _describeIndexes(string $table_name)
    {
    }

    protected function _describeUniqueKeys(string $table_name)
    {
    }

    protected function _describeForeignKeys(string $table_name)
    {
    }
}
