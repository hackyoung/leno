<?php
namespace Leno\Database;

use \Leno\Database\Constraint;

class Primary extends Constraint
{
    protected function equal($con1, $con2) : bool
    {
        return false;
    }

    protected function doSave($add, $remove)
    {
        $adapter = self::getAdapter();
        $adapter->beginTransaction();
        $adapter->execute('ALTER TABLE '.$this->table_name.'ADD CONSTRAINT PRIMARY KEY('.$add['primary'].')');
        self::getAdapter()->execute();
    }

    protected function getFromDB()
    {
        return [];
    }
}
