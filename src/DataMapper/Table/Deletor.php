<?php
namespace Leno\DataMapper\Table;

class Deletor extends \Leno\DataMapper\Table
{
    public function delete()
    {
        $this->execute($this->getSql());
    }

    public function getSql()
    {
        return sprintf('DELETE FROM %s %s WHERE %s',
            $this->getName(), $this->useJoin(),
            $this->useWhere()
        );
    }
}
