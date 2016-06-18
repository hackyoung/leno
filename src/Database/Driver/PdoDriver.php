<?php
namespace Leno\Database\Driver;

use \Leno\Database\Driver;

class PdoDriver extends Driver
{
    private $handler;

    private $parameters;

    public function __construct($params)
    {
        $this->parameters = $params;
        $handler_reflection = new \ReflectionClass('PDO');
        $this->handler = $handler_reflection->newInstanceArgs(
            $this->getArgs()
        );
        $this->handler->setAttribute(
            \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION
        );
    }

    public function getDB()
    {
        return $this->parameters['db'] ?? 'test_db';
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

    private function getArgs()
    {
        $params = $this->parameters;
        $dsn = ($params['adapter'] ?? 'mysql') . ':' . implode(';', [
            'dbname='. ($params['db'] ?? 'test_db'),
            'port='. ($params['port'] ?? null),
            'host='. ($params['host'] ?? 'localhost'),
        ]);
        return [
            $dsn,
            $params['user'] ?? null,
            $params['password'] ?? null,
            $params['options'] ?? null
        ];
    }
}
