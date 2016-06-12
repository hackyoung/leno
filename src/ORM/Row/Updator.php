<?php
namespace Leno\ORM\Row;

class Updator extends \Leno\ORM\Row
{
    public function update($data = null)
    {
        if(is_array($data)) {
            $this->setData($data);
        }
        $this->execute();
        return $this;
    }

    public function getSql()
    {
        $this->params = [];
        $data = $this->useData();
        if(!$data) {
            return false;
        }
        return sprintf('UPDATE %s %s %s WHERE %s',
            $this->getName(), $data,
            $this->useJoin(), $this->useWhere()
        );
    }

    public function setData(array $data)
    {
        foreach($data as $field => $value) {
            $this->set($field, $value);
        }
    }

    protected function useData()
    {
        $ret = [];
        foreach($this->data as $field=>$value) {
            if($value instanceof \Leno\ORM\Expr) {
                $idx = $value;
            } else {
                $idx = $this->setParam($field, $value);
            }
            $ret[] = sprintf('%s = %s', $this->getFieldExpr($field), $idx);
        }
        if(empty($ret)) {
            return false;
        }
        return 'SET ' . implode(', ', $ret);
    }
}
