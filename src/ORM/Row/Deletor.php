<?php
namespace Leno\ORM\Row;

class Deletor extends \Leno\ORM\Row
{
    public function delete()
    {
        $this->execute();
        return $this;
    }

    public function getSql()
    {
        $this->params = [];
        return sprintf('DELETE FROM %s %s WHERE %s',
            $this->getName(), $this->useJoin(),
            $this->useWhere()
        );
    }
}
