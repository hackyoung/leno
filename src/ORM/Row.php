<?php
namespace Leno\ORM;

/**
 *
 *   self::selector()
 *       ->quoteBegin()
 *           ->byEqHello('hello')
 *           ->or()
 *           ->byEqWorld('world')
 *       ->quoteEnd()->find();
 */
abstract class Row
{
    /**
     * @var 用于查询条件的OR关系
     */
    const R_OR = 'OR'; 

    /**
     * @var 用于查询条件的AND关系
     */
    const R_AND = 'AND';

    /**
     * @var 用于查询条件的优先级
     */
    const EXP_QUOTE_BEGIN = '(';

    /**
     * @var 用于查询条件的优先级
     */
    const EXP_QUOTE_END = ')';

    /**
     * @var 左连接类型
     */
    const JOIN_LEFT = 'LEFT_JOIN';

    /**
     * @var 内连接类型
     */
    const JOIN_INNER = 'INNER_JOIN';

    /**
     * @var 右连接类型
     */
    const JOIN_RIGHT = 'RIGHT_JOIN';

    /**
     * @var 外连接类型
     */
    const JOIN_OUTER = 'OUTER_JOIN';

    /**
     * @var 用于条件过滤
     */
    const TYPE_CONDI_BY = 'by';

    /**
     * @var 用于join操作
     */
    const TYPE_CONDI_ON = 'on';

    /**
     * @var 选择器类型
     */
    const TYPE_SELECTOR = 'selector';

    /**
     * @var 移除器类型
     */
    const TYPE_DELETOR = 'deletor';

    /**
     * @var 更新器类型
     */
    const TYPE_UPDATOR = 'updator';

    /**
     * @var 创建器类型
     */
    const TYPE_CREATOR = 'creator';

    /**
     * @var 已经创建的行操作器的实例 [
     *        selector_user => selector_object,
     *        updator_user => updator_object,
     *        deletor_user => deletor_object,
     *        creator_user => creator_object,
     * ]
     */
    protected static $instance = [];

    /**
     * @var 该行操作器所使用的adapter实例
     */
    private static $adapter_instance;

    /**
     * @var 该行操作器操作的表名
     */
    protected $table;

    /**
     * @var 保存where查询条件
     */
    protected $where = [];

    /**
     * @var 保存待join的selector
     */
    protected $joins = [];

    /**
     * @var 保存join的条件
     */
    protected $on = [];

    /**
     * @var 保存待插入或者更新的data [
     *        ['name' => 'young', 'age' => 25],
     * ]
     */
    protected $data = [];

    /**
     * @var 保存create,update,delete之后adapter返回的结果
     */
    protected $result;

    /**
     * @var 保存使用该行操作器的mapper,如果没有设置，则无法将查询出来的对象转换为对象
     */
    private $mapper;

    /**
     *  构造函数
     * @param string table 表明
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     *  __call方法，该方法提供by系列函数，on系列函数, set系列函数,get系列函数的入口
     * @param string method 方法名
     * @param array|null parameters 调用参数
     */
    public function __call($method, $parameters=null)
    {
        $series = explode('_', unCamelCase($method, '_'));
        if(!isset($series[0])) {
            throw new \Exception(get_called_class() . '::' . $method . ' Not Found');
        }
        $type = $series[0];
        array_splice($series, 0, 1);
        $condi = [self::TYPE_CONDI_BY, self::TYPE_CONDI_ON];
        if(in_array($type, $condi) && $ret = $this->callCondition($series, $parameters, $type)) {
           return $ret;
        }
        if($type === 'set') {
            return $this->set(implode('_', $series), $parameters[0]);
        }
        if($type === 'reset') {
            return $this->reset(implode('_', $series));
        }
        throw new \Exception(get_class() . '::' . $method . ' Not Found');
    }

    /**
     *  __get魔术方法，返回其字段对应值
     * @return mixed
     */
    public function __get($key)
    {
        if(preg_match('/^field/', $key)) {
            return $this->getFieldExpr(
                unCamelCase(strtolower(str_replace('field', '', $key)))
            );
        }
        throw new \Exception(get_class() . '::'.$key. ' Not Defined');
    }

    /**
     *  设置该行操作器的mapper
     * @param string mapper mapper类名
     * @return this
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     *  开始事务
     * @return this
     */
    public function begin()
    {
        self::beginTransaction();
        return $this;
    }

    /**
     *  结束事务
     * @return this
     */
    public function end()
    {
        self::commitTransaction();
        return $this;
    }

    /**
     *  join其他行操作器
     */
    public function join($row, $type = self::JOIN_LEFT)
    {
        $this->joins[] = [
            'row' => $row,
            'type' => $type,
        ];
        return $this;
    }

    public function reset($attr)
    {
        $this->$attr = [];
        if($attr === 'joins') {
            $this->on = [];
        }
    }

    public function resetAll()
    {
        $this->data = [];
        $this->joins = [];
        $this->on = [];
        $this->where = [];
        return $this;
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
        return $this;
    }

    public function by($expr, $field, $value)
    {
        $this->attachAdd();
        $this->where[] = [
            'expr' => $expr,
            'field' => $field,
            'value' => $value,
        ];
        return $this;
    }

    public function on($expr, $field, $value)
    {
        $this->attachAdd();
        $this->on[] = [
            'expr' => $expr,
            'field' => $field,
            'value' => $value,
        ];
        return $this;
    }

    public function or()
    {
        $this->where[] = self::R_OR;
        return $this;
    }

    public function and()
    {
        $this->where[] = self::R_AND;
        return $this;
    }

    public function onOr()
    {
        $this->on[] = self::R_OR;
        return $this;
    }

    public function onAnd()
    {
        $this->on[] = self::R_OR;
        return $this;
    }

    public function quoteBegin()
    {
        $this->attachAdd();
        $this->where[] = self::EXP_QUOTE_BEGIN;
        return $this;
    }

    public function onQuoteBegin()
    {
        $this->attachAdd();
        $this->on[] = self::EXP_QUOTE_BEGIN;
        return $this;
    }

    public function quoteEnd()
    {
        $this->where[] = self::EXP_QUOTE_END;
        return $this;
    }

    public function onQuoteEnd()
    {
        $this->on[] = self::EXP_QUOTE_END;
        return $this;
    }

    public function quote($str)
    {
        $Adapter = self::$adapter;
        return $Adapter::keyQuote($str);
    }

    public function getOn()
    {
        return $this->getCondition(self::TYPE_CONDI_ON);
    }

    public function getWhere()
    {
        return $this->getCondition(self::TYPE_CONDI_BY);
    }

    public function getName()
    {
        return new \Leno\ORM\Expr($this->quote($this->table));
    }

    public function getFieldExpr($field)
    {
        return new \Leno\ORM\Expr($this->getName() . '.' . $this->quote($field));
    }

    public static function beginTransaction() 
    {
        if(!self::getAdapter()->inTransaction()) {
            self::getAdapter()->beginTransaction();
        }
    }

    public static function commitTransaction()
    {
        $adapter = self::getAdapter();
        if($adapter->inTransaction()) {
            return $adapter->commit();
        }
    }

    public static function rollback()
    {
        self::getAdapter()->rollback();
    }

    public static function selector($table)
    {
        $key = self::getInstanceKey(self::TYPE_SELECTOR, $table);
        if(!isset(self::$instance[$key])) {
            self::$instance[$key] = new \Leno\ORM\Row\Selector($table);
        }
        return self::$instance[$key];
    }

    public static function creator($table)
    {
        $key = self::getInstanceKey(self::TYPE_CREATOR, $table);
        if(!isset(self::$instance[$key])) {
            self::$instance[$key] = new \Leno\ORM\Row\Creator($table);
        }
        return self::$instance[$key];
    }

    public static function deletor($table)
    {
        $key = self::getInstanceKey(self::TYPE_DELETOR, $table);
        if(!isset(self::$instance[$key])) {
            self::$instance[$key] = new \Leno\ORM\Row\Deletor($table);
        }
        return self::$instance[$key];
    }

    public static function updator($table)
    {
        $key = self::getInstanceKey(self::TYPE_UPDATOR, $table);
        if(!isset(self::$instance[$key])) {
            self::$instance[$key] = new \Leno\ORM\Row\Updator($table);
        }
        return self::$instance[$key];
    }

    public static function getAdapter()
    {
        if(!self::$adapter_instance) {
            self::$adapter_instance = \Leno\ORM\Connector::get();
        }
        return self::$adapter_instance;
    }

    public static function getInstanceKey($type, $table)
    {
        return $type.'_'.$table;
    }

    protected function useOn()
    {
        return implode(' ', $this->getOn());
    }

    protected function useWhere()
    {
        $ret = $this->getWhere();
        foreach($this->joins as $join) {
            $joinWhere = $join['row']->getWhere();
            if(!empty($joinWhere) && !empty($ret)) {
                $ret[] = self::R_AND;
            }
            $ret = array_merge($ret, $joinWhere);
        }
        return implode(' ', $ret);
    }

    protected function useJoin()
    {
        $map = [
            self::JOIN_INNER => 'INNER JOIN',
            self::JOIN_LEFT => 'LEFT JOIN',
            self::JOIN_RIGHT => 'RIGHT JOIN',
            self::JOIN_OUTER => 'OUTER JOIN',
        ];
        $ret = [];
        foreach($this->joins as $join) {
            $ret[] = sprintf('%s %s ON %s', 
                $map[$join['type']],
                $join['row']->getName(),
                $join['row']->useOn()
            );
        }
        return implode(' ', $ret);
    }

    protected function valueQuote($value)
    {
        if(is_string($value) && !$value instanceof \Leno\ORM\Expr) {
            return '\''.$value.'\'';  
        }
        return $value;
    }

    protected function getMapper()
    {
        if(!isset($this->mapper)) {
            throw new \Exception('Mapper Not Set');
        }
        return $this->mapper;
    }

    /**
     * call魔术方法调用该方法，处理$row->by..., $row->on...系列函数
     * @param array series 通过__call 传递的参数名得到的
     */
    private function callCondition($series, $value, $type=self::TYPE_CONDI_BY)
    {
        $exprs = [
            'gt', 'lt', 'gte', 'lte', 'in', 'eq', 'like',
        ];
        if(isset($series[0]) && $series[0] === 'not') {
            $not = true;
            array_splice($series, 0, 1);
        } else {
            $not = false;
        }
        if(!isset($series[0]) || !in_array($series[0], $exprs)) {
            return false;
        }
        if($not) {
            $expr = 'not_'.$series[0];
        } else {
            $expr = $series[0];
        }
        array_splice($series, 0, 1);
        $field = implode('_', $series);
        switch($type) {
            case self::TYPE_CONDI_ON:
                return $this->on($expr, $field, $value[0]);
            case self::TYPE_CONDI_BY:
                return $this->by($expr, $field, $value[0]);
        }
    }

    /**
     * 补齐默认的and关系
     */
    private function attachAdd()
    {
        $map = [
            self::R_OR,
            self::R_AND,
            self::EXP_QUOTE_BEGIN,
        ];
        $len = count($this->where);
        if($len > 0 && !in_array($this->where[$len - 1], $map)) {
            $this->and();
        }
    }

    /**
     * 构造查询条件,该方法可构造where条件以及join的on条件
     * @param string type self::TYPE_CONDI_BY|self::TYPE_CONDI_ON
     * @return array 查询条件的序列
     */
    private function getCondition($type)
    {
        switch($type) {
            case self::TYPE_CONDI_BY:
                $where = $this->where;
                break;
            case self::TYPE_CONDI_ON:
                $where = $this->on;
                break;
            default:
                return [];
        }
        $ret = [];
        $eq_arr = [
            self::EXP_QUOTE_BEGIN,
            self::EXP_QUOTE_END,
            self::R_OR,
            self::R_AND,
        ];
        foreach($where as $item) {
            if(in_array($item, $eq_arr)) {
                $ret[] = $item;
                continue;
            }
            $ret[] = $this->expr($item);
        }
        return $ret;
    }

    /**
     * 将表达式描述转换为SQL可识别的字符串
     * @param array item [
     *        'field' => 'field',
     *        'expr' => 'expr',
     *        'value' => 'value'
     * ]
     * @return string
     */
    private function expr($item)
    {
        $like = [ 'like' => 'LIKE', 'not_like' => 'NOT LIKE', ];
        $in = [ 'in' => 'IN', 'not_in' => 'NOT IN', ];
        $expr = [ 'eq' => '=', 'not_eq' => '!=', 'gt' => '>',
            'lt' => '<', 'gte' => '>=', 'lte' => '<=', ];
        if(isset($like[$item['expr']])) {
            return sprintf('%s %s %%s%', 
                $this->getFieldExpr($item['field']),
                $like[$item['expr']],
                $item['value']
            );
        } elseif (isset($in[$item['expr']])) {
            $format = '%s %s (%s)';
            $field = $this->getFieldExpr($item['field']);
            $expr = $in[$item['expr']];
            if ($item['value'] instanceof \Leno\ORM\Row) {
                $value = $item['value']->getSql();
            } elseif (is_array($item['value'])) {
                $value = implode(',', array_map(function($it) {
                    return $this->valueQuote($it);
                }, $item['value']));
            } else {
                $value = $item['value'];
            }
            return sprintf($format, $field, $expr, $value);
        } elseif (isset($expr[$item['expr']])) {
            return sprintf('%s %s %s', 
                $this->getFieldExpr($item['field']),
                $expr[$item['expr']],
                $this->valueQuote($item['value'])
            );
        } else {
            throw new \Exception($item['expr'] . ' Not Supported');
        }
    }

    /**
     * 获取执行sql之后的结果
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 执行sql语句，该方法不直接返回结果，返回一个this对象
     * 用self::getResult()获取执行结果
     * @param string|null sql 如果为null，则尝试生成sql
     * @return self
     */
    public function execute($sql = null)
    {
        if($sql === null) {
            $sql = $this->getSql();
        }
        if(!$sql || empty($sql)) {
            return false;
        }
        $driver = self::getAdapter();
        $this->result = $driver->exec($sql);
        if($this->result === false) {
            throw new \Exception(implode(':', $driver->errorInfo()). "\n");
        }
        return $this;
    }

    abstract public function getSql();
}
