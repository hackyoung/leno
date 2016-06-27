<?php
namespace Leno\Database;

interface AdapterInterface
{
    public function connect(): AdapterInterface;

    public function disconnect(): AdapterInterface;

    public function releaseSavePoint(): AdapterInterface;

    public function beginTransaction(): AdapterInterface;

    public function commitTransaction(): AdapterInterface;
}
