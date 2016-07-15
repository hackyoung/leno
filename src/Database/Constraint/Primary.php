<?php
namespace Leno\Database\Constraint;

use \Leno\Database\Constraint;

class Primary extends Constraint
{
    protected function equal($con1, $con2) : bool
    {
        return $con1 == $con2;
    }

    protected function doSave($new, $old)
    {
        $adapter = self::getAdapter();
        $adapter->beginTransaction();
        try {
            if ($old) {
                $adapter->execute('ALTER TABLE '.$this->table_name.' DROP PRIMARY KEY');
            }
            $adapter->execute('ALTER TABLE '.$this->table_name.' ADD CONSTRAINT PRIMARY KEY('.$new.')');
            $adapter->commitTransaction();
        } catch (\Exception $ex) {
            $adapter->rollback();
            throw $ex;
        }
    }

    protected function getFromDB()
    {
        return self::getAdapter()->describePrimaryKey($this->table_name);
    }
}
