<?php
namespace Leno\Database;

abstract class Adapter implements AdapterInterface
{
    private $driver;

    private $transaction_counter = 0;

    public function beginTransaction(): bool
    {
        if(!$this->transaction_counter++) {
            return $this->driver()->beginTransaction();
        }
        $this->execute('SAVEPOINT '.$this->getSavePoint());
        return $this->transactionCounter >= 0; 
    }

    public function commitTransaction(): bool
    {
        $save_point = $this->getSavePoint();
        if(!--$this->transaction_counter) {
           $this->releaseSavePoint($save_point);
           return $this->driver()->commit();
        }
        return $this->transaction_counter >= 0; 
    }

    public function releaseSavePoint(string $sp_pos): bool
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

    protected function driver()
    {
        if(!($this->driver instanceof DriverInterface)) {
            $this->driver = $this->getDriver();
        }
        return $this->driver;
    }

    private function getSavePoint()
    {
        return 'trans_'.$this->transaction_counter;
    }

    abstract protected function getDriver() : DriverInterface;

    abstract protected function quote(string $key) : string;
}
