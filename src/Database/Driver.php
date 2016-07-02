<?php
namespace Leno\Database;

abstract class Driver implements DriverInterface
{
    protected $max = 5;

    protected $busy = 0;

    protected $warning = 500*1000;

    protected static $driver_map = [
        'pdo' => '\\Leno\\Database\\Driver\\PdoDriver'
    ];

    public function rollback()
    {
        return $this->_rollback();
    }

    public function commit()
    {
        return $this->_commit();
    }

    public function beginTransaction()
    {
        return $this->_beginTransaction();
    }

    public function execute(string $sql, array $params = null)
    {
        $start_time = microtime(true);
        $this->busy++;
        try {
            $result = $this->_execute($sql, $params);
        } catch (\Exception $e) {
            $this->busy--;
            throw $e;
        }
        $use_time = $start_time - microtime(true);
        if ($use_time > $this->warning) {
            self::logger()->warn($sql.' executed use '.($user_time/1000).' Ms');
        }
        $this->busy--;
        return $result;
    }

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

    protected static function logger()
    {
        return logger('driver');
    }

    abstract protected function _rollback();

    abstract protected function _commit();

    abstract protected function _beginTransaction();

    abstract protected function _execute(string $sql, array $params);

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
