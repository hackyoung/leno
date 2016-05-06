<?php
namespace Leno\DataMapper\Row;

class Deletor extends \Leno\DataMapper\Row
{
    public function delete()
    {
        $this->execute();
        return $this;
    }

    public function getSql()
    {
        return sprintf('DELETE FROM %s %s WHERE %s',
            $this->getName(), $this->useJoin(),
            $this->useWhere()
        );
    }
}
