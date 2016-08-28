<?php
namespace Leno\Database\Row;

use \Leno\Database\Row;
use \Leno\Database\Expr;

class Selector extends Row
{
    /**
     * 降序
     */
    const ORDER_DESC = 'DESC';

    /**
     * 升序
     */
    const ORDER_ASC = 'ASC';

    /**
     * 保存分组信息
     */
    protected $group = [];

    /**
     * 保存排序信息
     */
    protected $order = [];

    /**
     * 保存查询字段信息
     */
    protected $field = [];

    /**
     * 保存limit信息
     */
    protected $limit = [];

    /**
     * __call魔术方法,提供group,order,field系列函数入口
     *
     * @param string method 方法名
     * @param mixed parameters 参数
     *
     * @return this
     */
    public function __call($method, array $args = [])
    {
        try {
            return parent::__call($method, $args);
        } catch(\Exception $ex) {
            $series = explode('_', unCamelCase($method, '_'));
            $type = $series[0];
            array_splice($series, 0, 1);
            array_unshift($args, implode('_', $series));
            switch($type) {
                case 'order':
                    return call_user_func_array([$this, 'order'], $args);
                case 'group':
                    return call_user_func_array([$this, 'group'], $args);
                case 'field':
                    return call_user_func_array([$this, 'field'], $args);
            }
            throw new \Exception(get_class() . '::' . $method . ' Not Found');
        }
    }

    /**
     * 排序
     *
     * @param string field 字段名
     * @param string self::ORDER_DESC|self::ORDER_ASC 排序方式
     *
     * @return this
     */
    public function order($field, $order = self::ORDER_ASC)
    {
        $this->order[] = [
            'field' => $field,
            'order' => $order
        ];
        return $this;
    }

    /**
     * 分组
     *
     * @param string field 字段名
     *
     * @return this
     */
    public function group($field)
    {
        $this->group[] = $field;
        return $this;
    }

    /**
     * 描述查询字段信息
     *
     * @param string field 字段名
     * @param string alias 查询别名
     *
     * ### example
     * $selector = new Selector('hello');
     *
     * $selector->field('name')             // 查询 name 字段
     *     ->field('cn_name', 'nick_name')  // 查询cn_name 字段 重命名为 nick_name
     *     ->field(new Expr('count(user_id)'), 'number') // 表达式查询
     *     ->field([                        // 数组方式
     *         'age',
     *         'unit' => 'tech_unit',
     *     ]);
     *
     * @return this
     */
    public function field($field, $alias=false)
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if(is_int($k)) {
                    $this->field($v);
                    continue;
                }
                $this->field($k, $v);
            }
            return $this;
        } elseif (is_string($field)) {
            $this->field[$field] = $alias;
            return $this;
        } elseif ($field instanceof Expr) {
            $field = '__expr__'.(string)$field;
            $this->field[$field] = $alias;
            return $this;
        } elseif ($field == false) {
            $this->field = false;
            return $this;
        }
        throw new \Exception('Field Type Not Surpported');
    }

    /**
     * 限制查询数量及偏移
     *
     * @param integer row 查询的起始行数
     * @param integer limit 查询的数据条数
     *
     * ### sample
     * $selector = new Selector('hello');
     * $selector->limit(1, 100);        // 查询1-100行数据
     * $selector->limit(10, 20);        // 查询10-30行数据
     * $selector->limit(10);            // 查询1-10行数据
     *
     * @return this
     */
    public function limit(int $row, int $limit = null)
    {
        if ($limit == null) {
            $row = 1;
            $limit = $row;
        }
        $this->limit = [
            'row' => $row,
            'limit' => $limit,
        ];
        return $this;
    }

    /**
     * 该方法会读取$this->field属性，然后返回
     * [
     *      'hello',
     *      'world AS the_world'
     * ]
     * 方式的数组
     */
    public function getField() : array
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

    /**
     * 该方法会读取$this->group属性，然后返回
     * [
     *      `table`.`field`,
     * ]
     * 的格式，"`"在不同的数据库系统的表现形式不一样
     */
    public function getGroup()
    {
        return array_map(function($field) {
            return $this->getFieldExpr($field);
        }, $this->group);
    }

    /**
     * 该方法会读取$this->order属性，然后返回
     * [
     *      `table`.`field` DESC,
     *      `table`.`field` ASC
     * ]
     * 的格式，"`"在不同的数据库系统的表现形式不一样
     */
    public function getOrder()
    {
        $ret = [];
        foreach($this->order as $order_field) {
            $field = (string)$order_field['field'];
            if (!($order_field['field'] instanceof Expr)) {
                $field = $this->getFieldExpr($order_field['field']);
            }
            $ret[] = $field . ' ' . $order_field['order'];
        }
        return $ret;
    }

    /**
     * 执行查找操作，如果设置了Entity类，该方法会将数据转换成Entity对象
     *
     * ### sample
     *
     * $selector = new Selector('table');
     * $selector->field([
     *    'hello',
     *    'world' => 'the_world'
     * ])->byEqHello('hello')
     * ->or()
     *  ->byEqHello('hell')
     *  ->find();
     */
    public function find()
    {
        $Entity = $this->entityClass;
        if ($Entity) {
            $attrs = $Entity::getAttributes();
            foreach($attrs as $field => $attr) {
                $this->field($field);
            }
        }
        $result = $this->execute();
        if(!$result || !$Entity) {
            return $result;
        }
        $ret = [];
        foreach($result as $k=>$row) {
            $ret[$k] = $Entity::newFromDB($row);
        }
        return $ret;
    }

    /**
     * 根据条件查找第一条数据
     */
    public function findOne()
    {
        $this->limit(0,1);
        $ret = $this->find() ?? [];
        return $ret[0] ?? false;
    }

    /**
     * count有多少条数据会被返回
     */
    public function count()
    {
        $Entity = $this->entityClass;
        if($Entity) {
            $field = $this->quote($Entity::$table.'.'.$Entity::$primary);
        } else {
            $field = '*';
        }
        $sql = sprintf('SELECT count(%s) as counter FROM %s %s WHERE %s %s %s',
            $field, $this->quote($this->table),
            $this->useJoin(), $this->useWhere(),
            $this->useGroup(), $this->useOrder()
        );
        $result = $this->execute($sql);
        if(!$result) {
            return 0;
        }
        foreach ($result as $k=>$row) {
            return $row['counter'];
        }
    }

    public function execute($sql = null)
    {
        $stmt = parent::execute($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt;
    }

    public function getSql()
    {
        $this->params = [];
        return sprintf('SELECT %s FROM %s %s WHERE %s %s %s %s',
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
        if(empty($this->limit)) {
            return '';
        }
        $limit = 'LIMIT ' . ($this->limit['limit'] ?? -1);
        if ($this->limit['row'] > 0) {
            $limit .= ' OFFSET ' . ($this->limit['row'] - 1);
        }
        return $limit;
    }
}
