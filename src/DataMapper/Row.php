<?php
namespace Leno\DataMapper;

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
    const R_OR = 'OR'; 

    const R_AND = 'AND';

	const EXP_QUOTE_BEGIN = '(';

	const EXP_QUOTE_END = ')';

	const JOIN_LEFT = 'LEFT_JOIN';

	const JOIN_INNER = 'INNER_JOIN';

	const JOIN_RIGHT = 'RIGHT_JOIN';

	const JOIN_OUTER = 'OUTER_JOIN';

    const TYPE_CONDI_BY = 'by';

    const TYPE_CONDI_ON = 'on';

	const TYPE_SELECTOR = 'selector';

	const TYPE_DELETOR = 'deletor';

	const TYPE_UPDATOR = 'updator';

	const TYPE_CREATOR = 'creator';

	protected static $instance = [];

	public static $adapter = '\Leno\DataMapper\Adapter\Mysql';

    protected $table;

    protected $where = [];

	protected $joins = [];

    protected $on = [];

    protected $data = [];

    private $mapper;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function __call($method, $parameters=null)
    {
        $series = explode('_', unCamelCase($method, '_'));
        if(!isset($series[0])) {
            throw new \Exception(get_class() . '::' . $method . ' Not Found');
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

	public function __get($key)
	{
		if(preg_match('/^field/', $key)) {
            return $this->getFieldExpr(
                unCamelCase(strtolower(str_replace('field', '', $key)))
            );
		}
        throw new \Exception(get_class() . '::'.$key. ' Not Defined');
	}

    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

	public function join($selector, $type = self::JOIN_LEFT)
	{
		$this->joins[] = [
			'selector' => $selector,
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
        return new \Leno\DataMapper\Expr($this->quote($this->table));
    }

    public function getFieldExpr($field)
    {
        return new \Leno\DataMapper\Expr($this->getName() . '.' . $this->quote($field));
    }

    public static function selector($table)
    {
		$key = self::getInstanceKey(self::TYPE_SELECTOR, $table);
		if(!isset(self::$instance[$key])) {
			self::$instance[$key] = new \Leno\DataMapper\Row\Selector($table);
		}
        return self::$instance[$key];
    }

    public static function creator($table)
    {
		$key = self::getInstanceKey(self::TYPE_CREATOR, $table);
		if(!isset(self::$instance[$key])) {
			self::$instance[$key] = new \Leno\DataMapper\Row\Creator($table);
		}
        return self::$instance[$key];
    }

    public static function deletor($table)
    {
		$key = self::getInstanceKey(self::TYPE_DELETOR, $table);
		if(!isset(self::$instance[$key])) {
			self::$instance[$key] = new \Leno\DataMapper\Row\Deletor($table);
		}
        return self::$instance[$key];
    }

    public static function updator($table)
    {
		$key = self::getInstanceKey(self::TYPE_UPDATOR, $table);
		if(!isset(self::$instance[$key])) {
			self::$instance[$key] = new \Leno\DataMapper\Row\Updator($table);
		}
        return self::$instance[$key];
    }

    public static function getAdapter()
    {
        return new self::$adapter;
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
            $ret[] = self::R_AND;
            $ret = array_merge($ret, $join['selector']->getWhere());
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
				$join['selector']->getName(),
				$join['selector']->useOn()
			);
		}
		return implode(' ', $ret);
	}

    protected function valueQuote($value)
    {
        if(is_string($value) && !$value instanceof \Leno\DataMapper\Expr) {
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

    private function callCondition($where, $value, $type=self::TYPE_CONDI_BY)
    {
        $exprs = [
            'gt', 'lt', 'gte', 'lte', 'in', 'eq', 'like',
        ];
        if(isset($where[0]) && $where[0] === 'not') {
            $not = true;
            array_splice($where, 0, 1);
		} else {
			$not = false;
		}
        if(!isset($where[0]) || !in_array($where[0], $exprs)) {
			return false;
        }
        if($not) {
            $expr = 'not_'.$where[0];
        } else {
            $expr = $where[0];
        }
        array_splice($where, 0, 1);
        $field = implode('_', $where);
        switch($type) {
            case self::TYPE_CONDI_ON:
                return $this->on($expr, $field, $value[0]);
            case self::TYPE_CONDI_BY:
                return $this->by($expr, $field, $value[0]);
        }
    }

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

	private function expr($item)
	{
		$like = [
			'like' => 'LIKE', 'not_like' => 'NOT LIKE',
		];
		$in = [
			'in' => '', 'not_in' => '',
		];
		$expr = [
			'eq' => '=', 'not_eq' => '!=', 'gt' => '>',
			'lt' => '<', 'gte' => '>=', 'lte' => '<=',
		];
		if(isset($like[$item['expr']])) {
			return sprintf('%s %s %%s%', 
				$this->quote($this->table) .'.'. $this->quote($item['field']),
				$like[$item['expr']],
				$item['value']
			);
		}
		if(isset($in[$item['expr']])) {
			return '';
		}
		if(isset($expr[$item['expr']])) {
			return sprintf('%s %s %s', 
				$this->quote($this->table) . '.' . $this->quote($item['field']),
				$expr[$item['expr']],
				$this->valueQuote($item['value'])
			);
		}
		throw new \Exception($item['expr'] . ' Not Supported');
	}

    public function execute($sql = null) {
        if($sql === null) {
            $sql = $this->getSql();
        }
		if(!$sql || empty($sql)) {
			return false;
		}
		$driver = self::getAdapter();
        return $driver->exec($sql) or die(implode(':', $driver->errorInfo()). "\n");
    }

    abstract public function getSql();
}
