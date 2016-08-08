<?php
namespace Leno\Database\Constraint;

use \Leno\Database\Adapter;
use \Leno\Database\Constraint;

class Unique extends Constraint
{
    protected function equal($con1, $con2) : bool
    {
        if (count($con1) != count($con2)) {
            return false;
        }
        foreach ($con1 as $con) {
            if (!in_array($con, $con2)) {
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
            foreach ($remove as $key => $columns) {
                $adapter->execute($sql . 'DROP INDEX '.$key);
            }
            foreach ($add as $key => $columns) {
                $sub_sql = sprintf(
                    'ADD CONSTRAINT %s UNIQUE KEY(%s)',
                    $key,
                    implode(', ', $columns)
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
        return self::getAdapter()->describeUniqueKeys($this->table_name);
    }
}
