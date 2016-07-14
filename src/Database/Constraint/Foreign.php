<?php
namespace Leno\Database\Constraint;

use \Leno\Database\Adapter;
use \Leno\Database\Constraint;

class Foreign extends Constraint
{
    protected function equal($con1, $con2) : bool
    {
        if ($con1['foreign_table'] != $con2['foreign_table']) {
            return false;
        }

        foreach ($con1['relation'] as $key => $val) {
            if (!isset($con2['relation'][$key])) {
                return false;
            }
            if ($con2['relation'][$key] != $val) {
                return false;
            }
        }
        return true;
    }

    protected function doSave($add, $remove)
    {
        $sql = 'ALTER TABLE '.$this->table_name.' ';
        $adapter = self::getAdapter();
        $adapter->beginTransaction();
        try {
            foreach ($remove as $key => $config) {
                $adapter->execute($sql . 'DROP FOREIGN KEY '.$key);
            }
            foreach ($add as $key => $config) {
                $sub_sql = sprintf(
                    'ADD CONSTRAINT %s FOREIGN KEY(%s) REFERENCES %s(%s)',
                    $key,
                    implode(',', array_keys($config['relation'])),
                    $config['foreign_table'], 
                    implode(',', array_values($config['relation']))
                );
                $adapter->execute($sql . $sub_sql);
            }
            $adapter->commitTransaction();
        } catch (\Exception $ex) {
            $adapter->rollback();
            throw $ex;
        }
    }

    protected function getFromDB()
    {
        return self::getAdapter()->describeForeignKeys($this->table_name);
    }
}
