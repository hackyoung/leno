<?php
namespace Leno\Database;

abstract class Driver implements DriverInterface
{
    public static $cache = [];

    protected $max = 5;

    protected $busy = 0;

    protected $warning = 100*1000;

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
        $cache_sour = $sql;
        if (is_array($params)) {
            $cache_sour .= implode(',', $params);
        }
        $cache_key = md5($cache_sour);
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        $start_time = microtime(true);
        $this->busy++;
        try {
            self::$cache[$cache_key] = $this->_execute($sql, $params);
        } catch (\Exception $e) {
            $this->busy--;
            throw $e;
        }
        $use_time = $start_time - microtime(true);
        if ($use_time > $this->warning) {
            self::logger()->warn($sql.' executed use '.($user_time/1000).' Ms');
        }
        $this->busy--;
        return self::$cache[$cache_key];
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

    public static function get(string $driver_label = 'pdo', $parameters = null)
    {
        $Driver = self::$driver_map[$driver_label] ?? false;
        if (!$Driver) {
            throw new \Leno\Exception ('不支持的driver: '.$driver_label);
        }
        $driver_reflection = new \ReflectionClass($Driver);
        return $driver_reflection->newInstanceArgs([$parameters]);
    }

    protected static function logger()
    {
        return logger('driver');
    }

    abstract public function getDB();

    abstract protected function _rollback();

    abstract protected function _commit();

    abstract protected function _beginTransaction();

    abstract protected function _execute(string $sql, array $params);
}
