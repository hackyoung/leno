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
            'CHARACTER_MAXIMUM_LENGTH as length, '.
            'COLUMN_DEFAULT as default_value,'.
            'IS_NULLABLE as is_nullable '.
        'FROM '.
            'information_schema.COLUMNS '.
        'WHERE '.
            'TABLE_NAME = \''.$table_name.'\' AND '.
            'TABLE_SCHEMA = \''.$this->getDB().'\'';
        $result = $this->execute($sql);
        $fields = [];
        foreach ($result as $row) {
            $attr = [
                'type' => strtoupper($row['type']),
                'is_nullable' => true
            ];
            if ($row['length']) {
                $attr['type'] .= '('.$row['length'].')';
            }
            if ($row['is_nullable'] === 'NO') {
                $attr['is_nullable'] = false;
            }
            if ($row['default_value']) {
                $attr['default'] = $row['default_value'];
            }
            $fields[$row['field']] = $attr;
        }
        return $fields;
    }

    protected function _describeIndexes(string $table_name)
    {
        $result = $this->execute('SHOW INDEXES FROM '.$table_name);

        $indexes = [];

        foreach ($result as $row) {
            if (!isset($indexes[$row['Key_name']])) {
                $indexes[$row['Key_name']] = [];
            }
            $indexes[$row['Key_name']][] = $row['Column_name'];
        }

        return $indexes;
    }

    protected function _describeUniqueKeys(string $table_name)
    {
        $sql = 'SELECT '.
            'CONSTRAINT_NAME AS name, '.
            'TABLE_NAME AS local_table, '.
            'COLUMN_NAME AS local_key, '.
            'REFERENCED_TABLE_NAME AS foreign_table, '.
            'REFERENCED_COLUMN_NAME AS foreign_key '.
        'FROM '.
            'information_schema.KEY_COLUMN_USAGE '.
        'WHERE '.
            'CONSTRAINT_NAME != \'PRIMARY\' AND '.
            'TABLE_NAME = \''.$table_name.'\' AND '.
            'REFERENCED_TABLE_NAME IS NULL AND '.
            'TABLE_SCHEMA = \''.$this->getDB().'\'';

        $result = $this->execute($sql);

        $constraint = [];

        foreach($result as $row) {
            if (!isset($constraint[$row['name']])) {
                $constraint[$row['name']] = [];
            }
            $constraint[$row['name']][] = $row['local_key'];
        }

        return $constraint;
    }

    protected function _describeForeignKeys(string $table_name)
    {
        $sql = 'SELECT '.
            'CONSTRAINT_NAME AS name, '.
            'TABLE_NAME AS local_table, '.
            'COLUMN_NAME AS local_key, '.
            'REFERENCED_TABLE_NAME AS foreign_table, '.
            'REFERENCED_COLUMN_NAME AS foreign_key '.
        'FROM '.
            'information_schema.KEY_COLUMN_USAGE '.
        'WHERE '.
            'TABLE_NAME = \''.$table_name.'\' AND '.
            'TABLE_SCHEMA = \''.$this->getDB().'\' AND '.
            'REFERENCED_TABLE_NAME IS NOT NULL';

        $result = $this->execute($sql);

        $constraint = [];

        foreach ($result as $row) {
            if (!isset($constraint[$row['name']])) {
                $constraint[$row['name']] = [
                    'foreign_table' => $row['foreign_table'],
                    'relation' => []
                ];
            }
            $constraint[$row['name']]['relation'][$row['local_key']] = $row['foreign_key'];
        }

        return $constraint;
    }

    protected function _describePrimaryKey(string $table_name)
    {
        $sql = 'SELECT '.
            'CONSTRAINT_NAME AS name, '.
            'COLUMN_NAME AS field '.
        'FROM '.
            'information_schema.KEY_COLUMN_USAGE '.
        'WHERE '.
            'CONSTRAINT_NAME = \'PRIMARY\' AND '.
            'TABLE_SCHEMA = \''.$this->getDB().'\' AND '.
            'TABLE_NAME = \''.$table_name.'\'';

        $result = $this->execute($sql);
        return $result->fetch()['field'] ?? null;
    }
}
