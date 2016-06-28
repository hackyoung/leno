<?php
namespace Leno\Database;

use \Leno\Database\DriverInterface;
use \Leno\Database\AdapterInterface;
use \Leno\Database\Connection;

abstract class Adapter implements AdapterInterface
{
    private $transaction_counter = 0;

    private static $adapter_map = [
        'mysql' => '\\Leno\\Database\\Adapter\\MysqlAdapter',
    ];

    public static function get()
    {
        return new self::$adapter_map['mysql'];
    }

    public function beginTransaction() : bool
    {
        if(!$this->transaction_counter++) {
            return $this->driver()->beginTransaction();
        }
        $this->execute('SAVEPOINT '.$this->getSavePoint());
        return $this->transactionCounter >= 0; 
    }

    public function commitTransaction() : bool
    {
        $save_point = $this->getSavePoint();
        if(!--$this->transaction_counter) {
           $this->releaseSavePoint($save_point);
           return $this->driver()->commit();
        }
        return $this->transaction_counter >= 0; 
    }

    public function releaseSavePoint(string $sp_pos) : bool
    {
        if($this->execute('RELEASE SAVEPOINT '.$sp_pos)) {
            return true;
        }
        return false;
    }

    public function rollback()
    {
        $save_point = $this->getSavePoint();
        if (--$this->transactionCounter) {
            $this->execute('ROLLBACK TO '.$save_point);
            return true;
        }
        return $this->driver()->rollback();
    }

    public function keyQuote(string $key) : string
    {
        return $this->quote($key);
    }

    public function execute($sql, $params = null)
    {
        return $this->driver()->execute($sql, $params);
    }

    protected function driver() : DriverInterface
    {
        if(!($this->driver instanceof DriverInterface)) {
            $this->driver = Connection::instance()->select();
        }
        return $this->driver;
    }

    private function getSavePoint()
    {
        return 'trans_'.$this->transaction_counter;
    }

    abstract protected function quote(string $key) : string;
}
