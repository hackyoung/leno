<?php
namespace Leno\Database;

class Driver
{
    protected $max = 5;

    protected $busy = 0;

    protected static $driver_map = [
        'pdo' => '\\Leno\\Database\\Driver\\PdoDriver'
    ];

    public function addWeight(int $weight)
    {
        $this->max += $weight;
        return $this;
    }

    public function getWeight() : int
    {
        return $this->max - $this->busy;
    }

    public static function get($driver_label = 'pdo', $parameters = null)
    {
        $Driver = self::$driver_map[$driver_label];
        $driver_reflection = new \ReflectionClass($Driver);
        switch($driver_label) {
            case 'pdo':
                $p = self::normalizePdoParams($parameters);
                return $driver_reflection->newInstanceArgs($p);
            default:
                throw new \Leno\Exception('不支持的driver');
        }
    }

    private static function normalizePdoParams($params)
    {
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
