<?php
namespace Leno\DataMapper\Table;

class Updator extends \Leno\DataMapper\Table
{
	public function update($data = null)
	{
		return $this->execute();
	}

    public function getSql()
    {
		$data = $this->useData();
		if(!$data) {
			return false;
		}
        return sprintf('UPDATE %s %s %s WHERE %s',
            $this->getName(), $data,
            $this->useJoin(), $this->useWhere()
        );
    }

    protected function useData()
    {
        $ret = [];
        foreach($this->data as $field=>$value) {
            $ret[] = sprintf('%s = %s', 
                $this->getFieldExpr($field),
                $this->valueQuote($value)
            );
        }
		if(empty($ret)) {
			return false;
		}
        return 'SET ' . implode(', ', $ret);
    }
}
