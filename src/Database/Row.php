<?php
namespace Leno\Database;

use \Leno\Database\Expr;
use \Leno\Database\Adapter;
use \Leno\ORM\EntityInterface;

abstract class Row
{
    /**
     * 用于查询条件的OR关系
     */
    const R_OR = 'OR'; 

    /**
     * 用于查询条件的AND关系
     */
    const R_AND = 'AND';

    /**
     * 用于查询条件的优先级
     */
    const EXP_QUOTE_BEGIN = '(';

    /**
     * 用于查询条件的优先级
     */
    const EXP_QUOTE_END = ')';

    /**
     * 左连接类型
     */
    const JOIN_LEFT = 'LEFT_JOIN';

    /**
     * 内连接类型
     */
    const JOIN_INNER = 'INNER_JOIN';

    /**
     * 右连接类型
     */
    const JOIN_RIGHT = 'RIGHT_JOIN';

    /**
     * 外连接类型
     */
    const JOIN_OUTER = 'OUTER_JOIN';

    /**
     * 用于条件过滤
     */
    const TYPE_CONDI_BY = 'by';

    /**
     * 用于join操作
     */
    const TYPE_CONDI_ON = 'on';

    const EXP_GT = 'gt';
    const EXP_GTE = 'gte';
    const EXP_LT = 'lt';
    const EXP_LTE = 'lte';
    const EXP_EQ = 'eq';
    const EXP_NOT_EQ = 'not_eq';
    const EXP_IN = 'in';    
    const EXP_NOT_IN = 'not_in';
    const EXP_LIKE = 'like';
    const EXP_NOT_LIKE = 'not_like';
    const EXP_EXPR = 'expr';
    const EXPR_NULL = 'null';
    const EXPR_NOT_NULL = 'not_null';

    /**
     * 该行操作器操作的表名
     */
    protected $table;

    /**
     * 保存where查询条件
     */
    protected $where = [];

    /**
     * 保存待join的selector
     */
    protected $joins = [];

    /**
     * 保存join的条件
     */
    protected $on = [];

    /**
     * 保存待插入或者更新的data [
     *        ['name' => 'young', 'age' => 25],
     * ]
     */
    protected $data = [];

    protected $params = [];

    /**
     * select之后转为的entityClass
     */
    protected $entityClass;

    /**
     * 构造函数
     *
     * @param string table 表明
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     *  __call方法，该方法提供by系列函数，on系列函数, set系列函数,get系列函数的入口
     *
     * @param string method 方法名
     * @param array|null parameters 调用参数
     *
     */
    public function __call($method, array $args = [])
    {
        $series = explode('_', unCamelCase($method, '_'));
        if(!isset($series[0])) {
            throw new \Exception(get_called_class() . '::' . $method . ' Not Found');
        }
        $first = array_splice($series, 0, 1)[0];
        $opers = array('set', 'reset');
        if (in_array($first, $opers)) {
            array_unshift($args, implode('_', $series));
            return call_user_func_array([$this, $first], $args);
        }
        $condi = [self::TYPE_CONDI_BY, self::TYPE_CONDI_ON];
        if(in_array($first, $condi) && $ret = $this->callCondition($series, $args, $first)) {
           return $ret;
        }
        throw new \Exception(get_class() . '::' . $method . ' Not Found');
    }

    /**
     * join其他行操作器
     *
     * ### example
     * $hello = (new Selector('hello'))
     *      ->field('name', 'hello_name')
     *      ->byNameEq('hello');
     *
     * $world = (new Selector('world'))
     *      ->field('name', 'world_name')
     *      ->join($hello->onIdEq($world->getFieldExpr('id')));
     *
     * $world->execute();
     *
     */
    public function join(Row $row, $type = self::JOIN_LEFT)
    {
        $this->joins[] = [
            'row' => $row,
            'type' => $type,
        ];
        return $this;
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    public function resetAll()
    {
        $this->data = [];
        $this->joins = [];
        $this->on = [];
        $this->where = [];
        $this->params = [];
        return $this;
    }

    /**
     * 设置data
     *
     * @param string field 字段名
     * @param string value 值
     *
     * @return this
     *
     * ### example
     *
     * $updator = new Updator('hello');
     * $updator->set('name', 'hello')   // 传统写法
     *      ->setAge(18)                // 简便写法
     *      ->update();
     */
    public function set(string $field, $value)
    {
        $this->data[$field] = $value;
        return $this;
    }

    /**
     * 通过条件过滤
     *
     * @param string expr 表达式
     * @param string field 字段名
     * @param mixed value 比较的值
     *
     * @return this
     *
     * ### example
     *
     * $updator = new Updator('hello'); 
     * $updator->setName('young')
     *      ->byNameEq('young')     // 简单写法
     *      ->update();
     *
     * $selector = new Selector('hello');
     * $selector->by('name', 'you', Row::EXP_LIKE) // 原生写法
     *      ->find();
     *
     */
    public function by($field, $value = null, $expr = self::EXP_EQ)
    {
        $Entity = \baseClass($this->entityClass);
        if ($this->entityClass && $value instanceof $Entity) {
            $reflection_entity = new \ReflectionClass($this->entityClass);
            $foreign = $reflection_entity->getStaticPropertyValue('foreign');
            if (!isset($foreign[$field])) {
                throw new \Leno\Exception ('Can\'t filter by entity without foreign setting');
            }
            $value = $value->get($foreign[$field]['foreign_key']);
            $field = $foreign[$field]['local_key'];
        }
        $this->attachAdd(self::TYPE_CONDI_BY);
        $this->where[] = [
            'expr' => $expr,
            'field' => $field,
            'value' => $value,
        ];
        return $this;
    }

    /**
     * join 连接条件
     *
     * @param string expr 表达式
     * @param string field 字段名
     * @param mixed value 比较的值
     *
     * @return this
     */
    public function on($field, $value = null, $expr = self::EXP_EQ)
    {
        $this->attachAdd(self::TYPE_CONDI_ON);
        $this->on[] = [
            'expr' => $expr,
            'field' => $field,
            'value' => $value,
        ];
        return $this;
    }

    /**
     * 或者逻辑链接
     *
     * ### example
     *
     * $hello = (new Selector('hello'))
     *      ->byLikeName('you')
     *        ->or()
     *      ->byLikeName('hello')
     *      ->find();
     */
    public function or()
    {
        $this->where[] = self::R_OR;
        return $this;
    }

    /**
     * 并且逻辑连接, 在by函数之间默认是and连接
     *
     * ### example
     *
     * $hello = (new Selector('hello'))
     *      ->byNameLike('you')
     *        ->and()   // 可省略，默认为and连接
     *      ->byAgeEq(15)
     *      ->find();
     *
     */
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
        $this->attachAdd(self::TYPE_CONDI_BY);
        $this->where[] = self::EXP_QUOTE_BEGIN;
        return $this;
    }

    public function quoteEnd()
    {
        $this->where[] = self::EXP_QUOTE_END;
        return $this;
    }

    public function onQuoteBegin()
    {
        $this->attachAdd(self::TYPE_CONDI_ON);
        $this->on[] = self::EXP_QUOTE_BEGIN;
        return $this;
    }

    public function onQuoteEnd()
    {
        $this->on[] = self::EXP_QUOTE_END;
        return $this;
    }

    public function quote($val)
    {
        $quoted = implode('.', array_map(function($item) {
            return self::getAdapter()->keyQuote($item);
        }, explode('.', $val)));
        return new Expr($quoted);
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
        return $this->quote($this->table);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParam($field, $value)
    {
        $field = preg_replace('/[\`\"\'\.]/', '', $field);
        $idx = ':'.$this->table .'_'. $field . '_' .randString(2);
        $this->params[$idx] = $value;
        return $idx;
    }

    public function getFieldExpr($field)
    {
        return $this->quote($this->table . '.' . $field);
    }

    public static function beginTransaction() 
    {
        self::getAdapter()->beginTransaction();
    }

    public static function commitTransaction()
    {
        return self::getAdapter()->commitTransaction();
    }

    public static function rollback()
    {
        self::getAdapter()->rollback();
    }

    public static function getAdapter()
    {
        return Adapter::get();
    }

    protected function useOn()
    {
        return implode(' ', $this->getOn());
    }

    protected function useWhere()
    {
        $ret = $this->getWhere();
        if(empty($ret)) {
            $ret = ['1 = 1'];
        }
        foreach($this->joins as $join) {
            $joinWhere = $join['row']->getWhere();
            if(!empty($joinWhere)) {
                $ret[] = self::R_AND;
            }
            $ret = array_merge($ret, $joinWhere);
            $this->params = array_merge($this->params, $join['row']->getParams());
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


    protected function getMapper()
    {
        if(!isset($this->mapper)) {
            throw new \Exception('Mapper Not Set');
        }
        return $this->mapper;
    }

    /**
     * call魔术方法调用该方法，处理$row->by..., $row->on...系列函数
     *
     * @param array series 通过__call 传递的参数名得到的
     */
    private function callCondition($series, array $args, $type=self::TYPE_CONDI_BY)
    {
        $exprs = [
            self::EXP_GT, self::EXP_LT, self::EXP_GTE, self::EXP_LTE,
            self::EXP_IN, self::EXP_EQ, self::EXP_LIKE, self::EXP_EXPR,
            self::EXP_NOT_IN, self::EXP_NOT_LIKE, self::EXP_NOT_EQ,
            self::EXPR_NULL, self::EXPR_NOT_NULL
        ];
        $expr = end($series);
        if (!in_array($expr, $exprs)) {
            $expr = self::EXP_EQ;
        } else {
            array_pop($series);
        }
        if (end($series) === 'not') {
            $expr = array_pop($series).'_'.$expr;
        }
        if (!in_array($expr, $exprs)) {
            throw new \Leno\Exception('不支持的表达式:'.$expr);
        }
        $field = implode('_', $series) ?? null;
        if (count($args) == 0) {
            $args = [null];
        }
        array_unshift($args, $field);
        $args[] = $expr;
        if (self::TYPE_CONDI_BY === $type) {
            return call_user_func_array([$this, 'by'], $args);
        } 
        return call_user_func_array([$this, 'on'], $args);
    }

    /**
     * 补齐默认的and关系
     */
    private function attachAdd($type)
    {
        switch($type) {
            case self::TYPE_CONDI_ON:
                $where = &$this->on;
                break;
            case self::TYPE_CONDI_BY:
                $where = &$this->where;
                break;
        }
        $map = [
            self::R_OR,
            self::R_AND,
            self::EXP_QUOTE_BEGIN,
        ];
        $len = count($where);
        if($len > 0 && !in_array($where[$len - 1], $map)) {
            $this->and();
        }
    }

    /**
     * 构造查询条件,该方法可构造where条件以及join的on条件
     *
     * @param string type self::TYPE_CONDI_BY|self::TYPE_CONDI_ON
     *
     * @return array 查询条件的序列
     */
    private function getCondition($type)
    {
        $ret = [];
        switch ($type) {
            case self::TYPE_CONDI_BY:
                $where = $this->where;
                break;
            case self::TYPE_CONDI_ON:
                $where = $this->on;
                break;
            default:
                return $ret;
        }
        $eq_arr = [
            self::EXP_QUOTE_BEGIN, self::EXP_QUOTE_END,
            self::R_OR, self::R_AND,
        ];
        foreach ($where as $item) {
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
     *
     * @param array item [
     *        'field' => 'field',
     *        'expr' => 'expr',
     *        'value' => 'value'
     * ]
     *
     * @return string
     */
    private function expr($item)
    {
        if($item['expr'] == 'expr') {
            return $item['value'];
        }
        return $this->exprR($item) ?? $this->exprLike($item) ?? $this->exprIn($item) ?? $this->exprNull($item);
    }

    private function exprNull($item)
    {
        $null = ['null' => 'IS NULL', 'not_null' => 'IS NOT NULL'];
        if (!isset($null[$item['expr']])) {
            return;
        }
        $field = $this->getFieldExpr($item['field']);
        return new Expr($field . ' ' . $null[$item['expr']]);
    }

    private function exprIn($item)
    {
        $in = [ 'in' => 'IN', 'not_in' => 'NOT IN' ];
        if (!isset($in[$item['expr']])) {
            return;
        }
        $field = $this->getFieldExpr($item['field']);
        $expr = $in[$item['expr']];
        if ($item['value'] instanceof self) {
            $selector = clone $item['value'];
            $item['value'] = '('.$selector->getSql().')';
            $this->params += $selector->getParams();
        } elseif (is_array($item['value'])) {
            $item['value'] = implode(',', array_map(function($it) use ($item) {
                return $this->setParam($item['field'], $it);
            }, $item['value']));
        }
        return sprintf('%s %s (%s)', $field, $expr, $item['value']);
    }

    private function exprLike($item)
    {
        $like = [ 'like' => 'LIKE', 'not_like' => 'NOT LIKE', ];
        if(!isset($like[$item['expr']])) {
            return;
        }
        return sprintf('%s %s %s', 
            $this->getFieldExpr($item['field']),
            $like[$item['expr']],
            $this->setParam($item['field'], '%'.$item['value'].'%')
        );
    }

    private function exprR($item)
    {
        $expr = [
            self::EXP_EQ => '=', self::EXP_NOT_EQ => '!=', self::EXP_GT => '>',
            self::EXP_LT => '<', self::EXP_GTE => '>=', self::EXP_LTE => '<='
        ];
        if (!isset($expr[$item['expr']])) {
            return;
        }
        if(!($item['value'] instanceof \Leno\Database\Expr)) {
            $item['value'] = $this->setParam($item['field'], $item['value']);
        } elseif ($item['value'] instanceof self) {
            $selector = clone $item['value'];
            $item['value'] = '('.$selector->getSql().')';
            $this->params += $selector->getParams();
        }
        return sprintf('%s %s %s',
            $this->getFieldExpr($item['field']),
            $expr[$item['expr']],
            $item['value']
        );
    }

    /**
     * 执行sql语句
     *
     * @param string|null sql 如果为null，则尝试生成sql
     *
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
        return self::getAdapter()->execute($sql, $this->params);
    }

    abstract public function getSql();
}
