<?php
namespace Leno\Database\Driver;

use \Leno\Database\Driver;

class PdoDriver extends Driver
{
    private $handler;

    public function __construct($dsn, $user, $pass, $options = null)
    {
        $this->handler = new \PDO($dsn, $user, $pass, $options);
        $this->handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    protected function _rollback()
    {
        return $this->handler->rollback();
    }

    protected function _commit()
    {
        return $this->handler->commit();
    }

    protected function _beginTransaction()
    {
        return $this->handler->beginTransaction();
    }

    protected function _execute(string $sql, array $params = null)
    {
        $stmt = $this->handler->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
