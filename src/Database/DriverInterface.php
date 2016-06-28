<?php
namespace Leno\Database;

interface DriverInterface
{
    public function getWeight() : int;

    public function rollback();

    public function commit();

    public function beginTransaction();

    public function execute(string $sql, array $params);
}
