<?php
namespace Leno\Database\Row;

use \Leno\Database\Row;

class Creator extends Row
{
    public function create()
    {
        return $this->execute();
    }

    public function set(string $field, $val)
    {
        if(count($this->data) === 0) {
            $this->newRow();
        }
        $idx = count($this->data) - 1;
        $this->data[$idx][$field] = $val;
        return $this;
    }

    public function newRow()
    {
        $this->data[] = [];
        return $this;
    }

    protected function useData()
    {
        $values = [];
        if(!isset($this->data[0])) {
            return false;
        }
        $field = array_map(function($field) {
            return $this->getFieldExpr($field);
        }, array_keys($this->data[0]));
        foreach($this->data as $data) {
            $values[] = '('.implode(',', array_map(function($value) {
                $this->params[] = $value;
                return '?';
            }, array_values($data))).')';
        }
        return ['field' => implode(',', $field), 'values' => implode(',', $values)];
    }

    public function getSql()
    {
        $this->params = [];
        $data = $this->useData();
        if(empty($data)) {
            return false;
        }
        return sprintf('INSERT INTO %s (%s) VALUES %s',
            $this->quote($this->table), $data['field'],
            $data['values']
        );
    }
}
