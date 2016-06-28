<?php
namespace Leno\DataBase;

abstract class Adapter implements AdapterInterface
{
    private $transaction_counter = 0;

    public function beginTransaction(): bool
    {
        if(!$this->transaction_counter++) {
            return parent::beginTransaction();
        }
        $sp = $this->getSavePoint();
        $this->execute('SAVEPOINT '.$sp);
        return $this->transactionCounter >= 0; 
    }

    public function commitTransaction(): bool
    {
        $sp = $this->getSavePoint();
        if(!--$this->transaction_counter) {
           $this->releaseSavePoint($sp);
           return parent::commit();
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
        $sp = $this->getSavePoint();
        if (--$this->transactionCounter) {
            $this->execute('ROLLBACK TO '.$sp);
            return true;
        }
        return parent::rollback();
    }

    private function getSavePoint()
    {
        return 'trans'.$this->transaction_counter;
    }
}
