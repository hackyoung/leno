<?php
namespace Leno\Database;

use \Leno\Database\DriverInterface;
use \Leno\Database\AdapterInterface;
use \Leno\Database\Connection;

abstract class Adapter implements AdapterInterface
{
    private $transaction_counter = 0;

    private $tables_info = [];

    private $driver;

    private static $adapters = [];

    private static $adapter_map = [
        'mysql' => '\\Leno\\Database\\Adapter\\MysqlAdapter',
        'pgsql' => '\\Leno\\Database\\Adapter\\PgsqlAdapter',
    ];

    private function __construct() {}

    public static function get($adapter_label = 'mysql')
    {
        if(!isset(self::$adapters[$adapter_label])) {
            self::$adapters[$adapter_label] = new self::$adapter_map[$adapter_label];
        }
        return self::$adapters[$adapter_label];
    }

    public function beginTransaction() : bool
    {
        if (!$this->transaction_counter++) {
            return $this->driver()->beginTransaction();
        }
        $this->execute('SAVEPOINT trans'.$this->transaction_counter);
        return $this->transaction_counter >= 0;
    }

    public function commitTransaction() : bool
    {
        if (!--$this->transaction_counter) {
            return $this->driver()->commit();
        }
        return $this->transaction_counter >= 0;
    }

    public function rollback()
    {
        if (--$this->transaction_counter) {
            $this->execute('ROLLBACK TO trans'.($this->transaction_counter + 1));
            return true;
        }
        return $this->driver()->rollback();
    }

    public function keyQuote(string $key) : string
    {
        return $this->quote($key);
    }

    public function execute(string $sql, $params = null)
    {
        self::logger()->info('execute sql: '.$sql, $params ?? []);
        try {
            return $this->driver()->execute($sql, $params);
        } catch (\Exception $e) {
            self::logger()->err('sql error: '.$e->getMessage());
            throw $e;
        }
    }

    public function asyncExecute(string $sql, $params = null)
    {
        async_execute([$this, 'execute'], [$sql, $params]);
    }

    public function describeColumns(string $table_name)
    {
        if (!isset($this->tables_info[$table_name])) {
            return $this->tables_info[$table_name] = $this->_describeTable($table_name);
        }
        return $this->tables_info[$table_name];
    }

    public function describeIndexes(string $table_name)
    {
        return $this->_describeIndexes($table_name);
    }

    public function describeForeignKeys(string $table_name)
    {
        return $this->_describeForeignKeys($table_name);
    }

    public function describeUniqueKeys(string $table_name)
    {
        return $this->_describeUniqueKeys($table_name);
    }

    public function driver() : DriverInterface
    {
        if(!($this->driver instanceof DriverInterface)) {
            $this->driver = Connection::instance()->select();
        }
        return $this->driver;
    }

    protected static function logger()
    {
        return logger('adapter');
    }

    abstract protected function quote(string $key) : string;

    abstract protected function _describeColumns(string $table_name);

    abstract protected function _describeIndexes(string $table_name);

    abstract protected function _describeForeignKeys(string $table_name);

    abstract protected function _describeUniqueKeys(string $table_name);
}
