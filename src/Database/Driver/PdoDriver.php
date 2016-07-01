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
        $this->handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
        $this->busy++;
        try {
            $stmt->execute($params);
        } catch (\Exception $e) {
            $this->busy--;
            throw $e;
        }
        $this->busy--;
        return $stmt;
    }
}
