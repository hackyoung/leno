<?php
namespace Leno\ORM\Row;

class Selector extends \Leno\ORM\Row
{
    /**
     * @var 降序
     */
    const ORDER_DESC = 'DESC';

    /**
     * @var 升序
     */
    const ORDER_ASC = 'ASC';

    /**
     * @var 保存分组信息
     */
    protected $group = [];

    /**
     * @var 保存排序信息
     */
    protected $order = [];

    /**
     * @var 保存查询字段信息
     */
    protected $field = [];

    /**
     * @var 保存limit信息
     */
    protected $limit = [];

    /**
     * @description __call魔术方法,提供group,order,field系列函数入口
     * @param string method 方法名
     * @param mixed parameters 参数
     * @return this
     */
    public function __call($method, $parameters=null)
    {
        try {
            return parent::__call($method, $parameters);
        } catch(\Exception $ex) {
            $series = explode('_', unCamelCase($method, '_'));
            $type = $series[0];
            array_splice($series, 0, 1);
            switch($type) {
                case 'order':
                    return $this->callOrder($series, $parameters);
                case 'group':
                    return $this->callGroup($series);
                case 'field':
                    return $this->callField($series);
            }
            throw new \Exception(get_class() . '::' . $method . ' Not Found');
        }
    }

    /**
     * @description 排序
     * @param string field 字段名
     * @param string self::ORDER_DESC|self::ORDER_ASC 排序方式
     * @return this
     */
    public function order($field, $order)
    {
        $this->order[$field] = $order;
        return $this;
    }

    /**
     * @description 分组
     * @param string field 字段名
     * @return this
     */
    public function group($field)
    {
        $this->group[] = $field;
        return $this;
    }

    /**
     * @description 描述查询字段信息
     * @param string field 字段名
     * @param string alias 查询别名
     * @return this
     */
    public function field($field, $alias=false)
    {
        if(is_array($field)) {
            $new_field = [];
            foreach($field as $k => $v) {
                if(is_int($k)) {
                    $new_field[$v] = false;
                    continue;
                }
                $new_field[$k] = $v;
            }
            $this->field = array_merge($this->field, $new_field);
            return $this;
        } elseif(is_string($field)) {
            $this->field[$field] = $alias;
            return $this;
        } elseif($field instanceof \Leno\ORM\Expr) {
            $field = '__expr__'.(string)$field;
            $this->field[$field] = $alias;
            return $this;
        } elseif($field == false) {
            $this->field = false;
            return $this;
        }
        throw new \Exception('Field Type Not Surpported');
    }

    public function limit($row, $limit = -1)
    {
        $this->limit = [
            'row' => $row,
            'limit' => $limit,
        ];
        return $this;
    }

    public function getField()
    {
        if($this->field === false) {
            return [];
        }
        if(empty($this->field)) {
            return [$this->quote($this->table).'.'.'*'];
        }
        $ret = [];
        foreach($this->field as $field=>$alias) {
            $f = str_replace('__expr__', '', $field);
            if($f === $field) {
                $f = $this->getName() . '.' . $this->quote($field);
            }
            if($alias) {
                $f .= ' AS ' . $alias;
            }
            $ret[] = $f;
        }
        return $ret;
    }

    public function getGroup()
    {
        return array_map(function($field) {
            return $this->quote($this->table) . '.' .$this->quote($field);
        }, $this->group);
    }

    public function getOrder()
    {
        $ret = [];
        foreach($this->order as $field=>$order) {
        
            $ret[] = $this->quote($this->table) . '.' .$this->quote($field) . ' ' . $order;
        }
        return $ret;
    }

    public function find()
    {
        $ret = [];
        $result = $this->execute();
        if(!$result) {
            return $result;
        }
        foreach($result as $k=>$row) {
            $ret[$k] = $this->toMapper($row);
        }
        return $ret;
    }

    public function findOne()
    {
        $this->limit(0,1);
        $ret = $this->find() ?? [];
        return $ret[0] ?? false;
    }

    public function count()
    {
        $data = $this->field(
            new \Leno\ORM\Expr('count(*)'), 'count'
        )->limit(1)
            ->execute()
            ->fetchAll();
        foreach($data as $row) {
            return (int)$row['count'];
        }
    }

    public function execute($sql = null)
    {
        parent::execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this->stmt;
    }

    public function getSql()
    {
        $this->params = [];
        return sprintf('SELECT %s FROM %s %s WHERE %s %s %s',
            $this->useField(), $this->quote($this->table),
            $this->useJoin(), $this->useWhere(), $this->useGroup(), 
            $this->useOrder(), $this->useLimit()
        );
    }

    protected function useField()
    {
        $fields = $this->getField();
        foreach($this->joins as $join) {
            $fields = array_merge($fields, $join['row']->getField());
        }
        return implode(', ', $fields);
    }

    protected function useGroup()
    {
        $group_fields = $this->getGroup();
        foreach($this->joins as $join) {
            $group_fields = array_merge($group_fields, $join['row']->getGroup());
        }
        if(count($group_fields) > 0) {
            return 'GROUP BY '. implode(', ', $group_fields);
        }
        return '';
    }

    protected function useOrder()
    {
        $order_fields = $this->getOrder();
        foreach($this->joins as $join) {
            $order_fields = array_merge($order_fields, $join['row']->getOrder());
        }
        if(count($order_fields) > 0) {
            return 'ORDER BY ' . implode(', ', $order_fields);
        }
        return '';
    }

    protected function useLimit()
    {
        return sprintf('LIMIT %s, %s',
            $this->limit['row'] ?? 0,
            $this->limit['limit'] ?? -1
        );
    }

    private function toMapper($row)
    {
        $Mapper = $this->getMapper();
        $mapper = (new $Mapper($row))->setFresh(false);
        return $mapper;
    }

    private function callGroup($series)
    {
        $field = implode('_', $series);
        return $this->group($field);
    }

    private function callOrder($series, $order)
    {
        $field = implode('_', $series);
        return $this->order($field, $order[0] ?? self::ORDER_ASC);
    }

    private function callField($series, $alias)
    {
        $field = implode('_', $series);
        return $this->field($field, $alias[0] ?? false);
    }
}
