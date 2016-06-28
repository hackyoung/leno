<?php
namespace Leno\Database\Driver;

use \Leno\Database\DriverInterface;

class PdoDriver implements DriverInterface
{
    private $connection;

    public function __construct()
    {
    }

    public function rollback()
    {
        return $this->connection->rollback();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function execute($sql, $params = null)
    {
        $stmt = $this->connection->prepare($sql);
        logger()->info('EXECUTING SQL: '.$sql, $params);
        $result = $stmt->execute($params);
        if(!$result) {
        
        }
        return $stmt;
    }
}
