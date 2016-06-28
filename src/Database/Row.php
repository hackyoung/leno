<?php
namespace Leno\Database;

use \Leno\Database\Expr;
use \Leno\Database\Adapter;

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

    /**
     * 保存create,update,delete之后adapter返回的结果
     */
    protected $result;

    protected $params = [];

    protected $param_dirty = false;

    /**
     * 构造函数
     *
     * @param string table 表明
     */
    public function __construct($table)
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
     *
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
     * join其他行操作器
     *
     * ### example
     * $hello = (new Selector('hello'))
     *      ->field('name', 'hello_name')
     *      ->byEqName('hello');
     *
     * $world = (new Selector('world'))
     *      ->field('name', 'world_name')
     *      ->join($hello->onEqId($world->getFieldExpr('id')));
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
     *      ->byEqName('young')     // 简单写法
     *      ->update();
     *
     * $selector = new Selector('hello');
     * $selector->by('like', 'name', 'you') // 原生写法
     *      ->find();
     *
     */
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

    /**
     * join 连接条件
     *
     * @param string expr 表达式
     * @param string field 字段名
     * @param mixed value 比较的值
     *
     * @return this
     */
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
     *      ->byLikeName('you')
     *        ->and()   // 可省略，默认为and连接
     *      ->byEqAge(15)
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
        $this->attachAdd();
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
        $this->attachAdd();
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
        $idx = ':'.$this->table .'_'. $field . '_' .randString(16);
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
        return self::getAdapter()->commit();
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
            $ret = [1];
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
     *
     * @param string type self::TYPE_CONDI_BY|self::TYPE_CONDI_ON
     *
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
        return $this->exprLike($item) ?? $this->exprIn($item) ?? $this->exprR($item);
    }

    private function exprIn($item)
    {
        $in = [ 'in' => 'IN', 'not_in' => 'NOT IN', ];
        if (isset($in[$item['expr']])) {
            $format = '%s %s (%s)';
            $field = $this->getFieldExpr($item['field']);
            $expr = $in[$item['expr']];
            if ($item['value'] instanceof self) {
                $value = $item['value']->getSql();
                $this->params = array_merge($this->params, $item['value']->getParams());
            } elseif (is_array($item['value'])) {
                $value = implode(',', array_map(function($it) use ($item) {
                    $param_idx = $this->setParam($item['field'], $it);
                    return $param_idx;
                }, $item['value']));
            } else {
                $value = $item['value'];
            }
            return sprintf($format, $field, $expr, $value);
        }
    }

    private function exprLike($item)
    {
        $like = [ 'like' => 'LIKE', 'not_like' => 'NOT LIKE', ];
        if(isset($like[$item['expr']])) {
            $idx = $this->setParam($item['field'], '%'.$item['value'].'%');
            return sprintf('%s %s %s', 
                $this->getFieldExpr($item['field']),
                $like[$item['expr']],
                $idx
            );
        }
    }

    private function exprR($item)
    {
        $expr = [ 'eq' => '=', 'not_eq' => '!=', 'gt' => '>',
            'lt' => '<', 'gte' => '>=', 'lte' => '<=', ];
        if (isset($expr[$item['expr']])) {
            if(!($item['value'] instanceof \Leno\Database\Expr)) {
                $idx = $this->setParam($item['field'], $item['value']);
            } else {
                $idx = $item['value'];
            }
            return sprintf('%s %s %s',
                    $this->getFieldExpr($item['field']),
                    $expr[$item['expr']],
                    $idx
            );
        }
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
