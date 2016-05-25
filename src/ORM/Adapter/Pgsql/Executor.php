<?php
namespace Leno\ORM\Adapter\Pgsql;

class Executor extends \Leno\ORM\Adapter\Executor
{
    public function keyQuote(string $key)
    {
        return '"'.$key.'"';
    }

    public function getTableInfo(\Leno\ORM\Table $table)
    {
    }
}
