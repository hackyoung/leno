<?php
namespace Leno\Database;

use \Leno\Database\DriverInterface;
use \Leno\Database\AdapterInterface;
use \Leno\Database\Connection;

abstract class Adapter implements AdapterInterface
{
    private $transaction_counter = 0;

    private $tables_column = [];

    private $tables_index = [];

    private $tables_foreign_key = [];

    private $tables_unique_key = [];

    private $tables_primary_key = [];

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
        if (!isset($this->tables_column[$table_name])) {
            return $this->tables_column[$table_name] = $this->_describeColumns($table_name);
        }
        return $this->tables_column[$table_name];
    }

    public function describeIndexes(string $table_name) {
        if (!isset($this->tables_index[$table_name])) {
            return $this->tables_index[$table_name] = $this->_describeIndexes($table_name);
        }
        return $this->tables_index[$table_name];
    }

    public function describeForeignKeys(string $table_name)
    {
        if (!isset($this->tables_foreign_key[$table_name])) {
            return $this->tables_foreign_key[$table_name] = $this->_describeForeignKeys($table_name);
        }
        return $this->tables_foreign_key[$table_name];
    }

    public function describeUniqueKeys(string $table_name)
    {
        if (!isset($this->tables_unique_key[$table_name])) {
            return $this->tables_unique_key[$table_name] = $this->_describeUniqueKeys($table_name);
        }
        return $this->tables_unique_key[$table_name];
    }

    public function describePrimaryKey(string $table_name)
    {
        if (!isset($this->tables_primary_key[$table_name])) {
            return $this->tables_primary_key[$table_name] = $this->_describePrimaryKey($table_name);
        }
        return $this->tables_primary_key[$table_name];
    }

    public function driver() : DriverInterface
    {
        if(!($this->driver instanceof DriverInterface)) {
            $this->driver = Connection::instance()->select();
        }
        return $this->driver;
    }

    public function getDB()
    {
        return $this->driver()->getDB();
    }

    protected static function logger()
    {
        return logger('adapter');
    }

    abstract protected function quote(string $key) : string;

    /**
     * @return [
     *      'field_name' => ['type' => '', 'null' => '', 'default' => '']
     * ]
     */
    abstract protected function _describeColumns(string $table_name);

    /**
     * @return [
     *      'constraint_name' => ['field_name']
     * ]
     */
    abstract protected function _describeIndexes(string $table_name);

    /**
     * @return [
     *      'constraint_name' => [local => [], table => 'table_name', foreign => []]
     * ]
     */
    abstract protected function _describeForeignKeys(string $table_name);

    /**
     * @return [
     *      'constraint_name' => ['field_name']
     * ]
     */
    abstract protected function _describeUniqueKeys(string $table_name);

    abstract protected function _describePrimaryKey(string $table_name);
}
