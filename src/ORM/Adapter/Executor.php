<?php
namespace Leno\ORM\Adapter;

abstract class Executor extends \PDO
{
    protected static $type_map = [];

    protected $transaction_counter = 0;

    abstract public function getTableInfo(\Leno\ORM\Table $table);

    abstract public function keyQuote(string $key) : string;

    public function getTypeClass($label)
    {
        return self::$type_map[$label] ?? false;
    }

    public static function registerType($label, $class)
    {
        self::$type_map[$label] = $class;   
    }

    public function beginTransaction()
    {
       if(!$this->transaction_counter++) {
            return parent::beginTransaction();
       }
       $this->exec('SAVEPOINT trans'.$this->transaction_counter);
       return $this->transactionCounter >= 0; 
    }

    public function commit()
    {
       if(!--$this->transaction_counter) {
           return parent::commit();
       }
       return $this->transaction_counter >= 0; 
    }

    public function rollback()
    {
        if (--$this->transactionCounter) {
            $this->exec('ROLLBACK TO trans'.$this->transaction_counter + 1);
            return true;
        }
        return parent::rollback();
    }
}
