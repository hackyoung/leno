<?php
namespace \Leno\ORM\Adapter;

abstract class Executor extends \PDO
{
    protected static $type_map = [];

    abstract public function getTableInfo(\Leno\ORM\Table $table);

    abstract public function keyQuote(string $key);

    public function getTypeClass($label)
    {
        return self::$type_map[$label] ?? false;
    }

    public static function registerType($label, $class)
    {
        self::$type_map[$label] = $class;   
    }
}
