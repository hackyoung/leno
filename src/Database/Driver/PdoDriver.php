<?php
namespace Leno\Database\Driver;

use \Leno\Database\DriverInterface;
use \Leno\Database\Driver;

class PdoDriver extends Driver implements DriverInterface
{
    private $handler;

    public function __construct($dsn, $user, $pass, $options = null)
    {
        $this->handler = new \PDO($dsn, $user, $pass, $options);
    }

    public function rollback()
    {
        return $this->handler->rollback();
    }

    public function commit()
    {
        return $this->handler->commit();
    }

    public function beginTransaction()
    {
        return $this->handler->beginTransaction();
    }

    public function execute(string $sql, array $params = null)
    {
        $stmt = $this->handler->prepare($sql);
        logger()->info('EXECUTING SQL: '.$sql, $params);
        $this->busy++;
        $result = $stmt->execute($params);
        $this->busy--;
        if(!$result) {
            // TODO 处理异常
        }
        return $stmt;
    }
}
